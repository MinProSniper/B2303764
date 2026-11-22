const ESCROW_CONTRACT   = "0x5FbDB2315678afecb367f032d93F642f64180aa3";
const FREELANCER_WALLET = "0x3d31aa949ba85ea2bba1d2ce0c8db5bc0a90b1fa";

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
    btn.innerText = `🔐 Ví Web3: ${addr.slice(0, 6)}...${addr.slice(-4)} (Chain ${chainId})`;
  }
  return { provider, signer, addr };
}

async function signEscrowTx(event, form, msId, amountUsd, actionType) {
  event.preventDefault();
  const wallet = await connectMetaMask(activeChainId);
  if (!wallet) return;

  try {
    const ethValue = Math.max(0.001, amountUsd / 10000).toFixed(4);
    const targetTo = actionType === "deposit" ? ESCROW_CONTRACT : FREELANCER_WALLET;

    // 1. Tạo và ký giao dịch mật mã ECDSA trực tiếp trong trình duyệt
    const populatedTx = await wallet.signer.populateTransaction({
      to: targetTo,
      value: ethers.parseEther(ethValue)
    });
    const signedRawTx = await wallet.signer.signTransaction(populatedTx);

    // 2. Tính mã băm Keccak-256 chuẩn Ethereum của giao dịch đã ký
    const exactTxHash = ethers.keccak256(signedRawTx);

    // 3. Gửi giao dịch đã ký kèm Keccak-256 hash lên PHP JSON-RPC Node
    const rpcRes = await fetch(getRpcUrl(activeChainId), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        jsonrpc: "2.0",
        id: 1,
        method: "eth_sendRawTransaction",
        params: [signedRawTx, exactTxHash, wallet.addr, targetTo]
      })
    });
    const rpcData = await rpcRes.json();

    // 4. Lấy biên lai xác nhận khối (Transaction Receipt)
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

    // 5. Đính kèm biên lai giao dịch vào form và gửi về actions.php
    const fields = {
      tx_hash: rpcData.result,
      tx_from: wallet.addr,
      tx_to: targetTo,
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
    alert("Lỗi ký giao dịch Web3: " + err.message);
  }
}

window.addEventListener("DOMContentLoaded", () => {
  if (typeof ethers !== "undefined") {
    connectMetaMask(1337);
  }
});