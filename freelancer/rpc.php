<?php
// Pure PHP Ethereum JSON-RPC Server for MetaMask & ethers.js (Zero Node.js / Zero Docker)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$chainId = isset($_GET['chain']) && $_GET['chain'] == '1338' ? 1338 : 1337;
$chainHex = '0x' . dechex($chainId); // 0x539 (1337) or 0x53a (1338)

$raw = file_get_contents('php://input');
$req = json_decode($raw, true);
if (!$req) {
    echo json_encode(['jsonrpc' => '2.0', 'id' => 1, 'result' => 'PHP JSON-RPC Node Active']);
    exit;
}

// Handle batch or single JSON-RPC call
$isBatch = isset($req[0]);
$calls = $isBatch ? $req : [$req];
$responses = [];

// Latest block number from DB
$latestBlock = (int)$pdo->query("SELECT COALESCE(MAX(GDB_khoiblock), 92400) FROM GIAO_DICH_BLOCKCHAIN")->fetchColumn();

foreach ($calls as $call) {
    $id     = $call['id'] ?? 1;
    $method = $call['method'] ?? '';
    $params = $call['params'] ?? [];
    $result = null;

    switch ($method) {
        case 'eth_chainId':
            $result = $chainHex;
            break;

        case 'net_version':
            $result = (string)$chainId;
            break;

        case 'eth_blockNumber':
            $result = '0x' . dechex($latestBlock);
            break;

        case 'eth_gasPrice':
            $result = '0x3b9aca00'; // 1 Gwei
            break;

        case 'eth_estimateGas':
            $result = '0x14820'; // 84,000 gas
            break;

        case 'eth_getBalance':
            // Give any MetaMask test wallet 100 ETH balance on local chain
            $result = '0x56bc75e2d63100000'; // 100 ETH in Wei
            break;

        case 'eth_getTransactionCount':
            $count = (int)$pdo->query("SELECT COUNT(*) FROM GIAO_DICH_BLOCKCHAIN")->fetchColumn();
            $result = '0x' . dechex($count);
            break;

        case 'eth_sendRawTransaction':
            $rawTx  = $params[0] ?? ('0x' . bin2hex(random_bytes(32)));
            $txHash = !empty($params[1]) ? $params[1] : ('0x' . hash('sha256', $rawTx . microtime(true)));
            $txFrom = !empty($params[2]) ? $params[2] : '0x8b9605af2d460dd1018a0d5048fbb53afffda29b';
            $txTo   = !empty($params[3]) ? $params[3] : '0x5FbDB2315678afecb367f032d93F642f64180aa3';
            $latestBlock++;

            $receiptDir = __DIR__ . '/uploads/tx_receipts';
            if (!is_dir($receiptDir)) mkdir($receiptDir, 0777, true);

            $receipt = [
                'transactionHash'   => $txHash,
                'transactionIndex'  => '0x0',
                'blockHash'         => '0x' . hash('sha256', 'BLOCK_' . $latestBlock),
                'blockNumber'       => '0x' . dechex($latestBlock),
                'from'              => $txFrom,
                'to'                => $txTo,
                'cumulativeGasUsed' => '0x14820',
                'gasUsed'           => '0x14820',
                'effectiveGasPrice' => '0x3b9aca00',
                'status'            => '0x1',
                'logs'              => []
            ];
            file_put_contents("$receiptDir/$txHash.json", json_encode($receipt));
            $result = $txHash;
            break;

        case 'eth_getTransactionReceipt':
            $txHash = $params[0] ?? '';
            $file = __DIR__ . "/uploads/tx_receipts/$txHash.json";
            $result = file_exists($file) ? json_decode(file_get_contents($file), true) : null;
            break;

        case 'eth_getBlockByNumber':
            $reqNum = isset($params[0]) && $params[0] !== 'latest' ? hexdec($params[0]) : $latestBlock;
            $result = [
                'number'           => '0x' . dechex($reqNum),
                'hash'             => '0x' . hash('sha256', 'BLOCK_' . $reqNum),
                'parentHash'       => '0x' . hash('sha256', 'BLOCK_' . max(0, $reqNum - 1)),
                'nonce'            => '0x0000000000000000',
                'sha3Uncles'       => '0x1dcc4de8dec75d7aab85b567b6ccd41ad312451b948a7413f0a142fd40d49347',
                'logsBloom'        => '0x' . str_repeat('0', 512),
                'transactionsRoot' => '0x56e81f171bcc55a6ff8345e692c0f86e5b48e01b996cadc001622fb5e363b421',
                'stateRoot'        => '0x56e81f171bcc55a6ff8345e692c0f86e5b48e01b996cadc001622fb5e363b421',
                'receiptsRoot'     => '0x56e81f171bcc55a6ff8345e692c0f86e5b48e01b996cadc001622fb5e363b421',
                'miner'            => '0x0000000000000000000000000000000000000000',
                'difficulty'       => '0x1',
                'totalDifficulty'  => '0x1',
                'extraData'        => '0x',
                'size'             => '0x3e8',
                'gasLimit'         => '0x1c9c380',
                'gasUsed'          => '0x14820',
                'timestamp'        => '0x' . dechex(time()),
                'baseFeePerGas'    => '0x3b9aca00',
                'transactions'     => []
            ];
            break;

        default:
            $result = '0x0';
    }

    $responses[] = ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
}

echo json_encode($isBatch ? $responses : $responses[0]);