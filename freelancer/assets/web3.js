const ESCROW_CONTRACT   = "0x5FbDB2315678afecb367f032d93F642f64180aa3";
const FREELANCER_WALLET = "0x3d31aa949ba85ea2bba1d2ce0c8db5bc0a90b1fa";

const ESCROW_ABI = [
  "function depositMilestone(uint256 _milestoneId, address _freelancer) external payable",
  "function releaseMilestone(uint256 _milestoneId) external",
  "event MilestoneFunded(uint256 indexed milestoneId, address indexed client, uint256 amount, uint256 timestamp)",
  "event MilestoneReleased(uint256 indexed milestoneId, address indexed freelancer, uint256 amount, uint256 timestamp)"
];

let activeChainId = 1337;

function getRpcUrl(chainId) {
  const base = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, "");
  return `${base}/rpc.php?chain=${chainId}`;
}

function getEmbeddedWallet(provider) {
  let privKey = localStorage.getItem("freelance_embedded_pk");
  if (!privKey) {
    const randomWallet = ethers.Wallet.createRandom();
    privKey = randomWallet.privateKey;
    localStorage.setItem("freelance_embedded_pk", privKey);
  }
  return new ethers.Wallet(privKey, provider);
}

async function connectMetaMask(chainId = 1337) {
  activeChainId = chainId;
  const provider = new ethers.JsonRpcProvider(getRpcUrl(chainId));
  const signer   = getEmbeddedWallet(provider);
  const addr     = await signer.getAddress();

  const btn = document.getElementById("walletStatus");
  if (btn) {
    btn.innerText = `Ví Web3: ${addr.slice(0, 6)}...${addr.slice(-4)} (Chain ${chainId})`;
  }
  return { provider, signer, addr };
}

async function signEscrowTx(event, form, msId, amountUsd, actionType) {
  event.preventDefault();
  const wallet = await connectMetaMask(activeChainId);
  if (!wallet) return;

  try {
    const contract = new ethers.Contract(ESCROW_CONTRACT, ESCROW_ABI, wallet.signer);
    const ethValue = Math.max(0.001, amountUsd / 10000).toFixed(4);

    // Mã hóa dữ liệu gọi hàm Smart Contract (ABI calldata)
    let populatedTx;
    if (actionType === "deposit") {
      populatedTx = await contract.depositMilestone.populateTransaction(
        msId,
        FREELANCER_WALLET,
        { value: ethers.parseEther(ethValue) }
      );
    } else {
      populatedTx = await contract.releaseMilestone.populateTransaction(msId);
    }

    // Hoàn thiện thông số giao dịch & ký mật mã ECDSA
    const fullTx      = await wallet.signer.populateTransaction(populatedTx);
    const signedRawTx = await wallet.signer.signTransaction(fullTx);
    const exactTxHash = ethers.keccak256(signedRawTx);

    // Gửi giao dịch Smart Contract lên PHP JSON-RPC Node
    const rpcRes = await fetch(getRpcUrl(activeChainId), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        jsonrpc: "2.0",
        id: 1,
        method: "eth_sendRawTransaction",
        params: [signedRawTx, exactTxHash, wallet.addr, ESCROW_CONTRACT, fullTx.data]
      })
    });
    const rpcData = await rpcRes.json();

    // Lấy biên lai thực thi hợp đồng thông minh (Transaction Receipt)
    const receiptRes = await fetch(getRpcUrl(activeChainId), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        jsonrpc: "2.0",
        id: 2,
        method: "eth_getTransactionReceipt",
        params: [rpcData.result]
      })
    });
    const receiptData = await receiptRes.json();
    const blockNumber = parseInt(receiptData.result.blockNumber, 16);

    const fields = {
      tx_hash: rpcData.result,
      tx_from: wallet.addr,
      tx_to: ESCROW_CONTRACT,
      tx_block: blockNumber,
      tx_gas: "0.000084"
    };

    for (const [k, v] of Object.entries(fields)) {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = k;
      input.value = v;
      form.appendChild(input);
    }
    form.submit();
  } catch (err) {
    alert("Lỗi thực thi Smart Contract: " + err.message);
  }
}

window.addEventListener("DOMContentLoaded", () => {
  if (typeof ethers !== "undefined") {
    connectMetaMask(1337);
  }
});