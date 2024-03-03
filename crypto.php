<!DOCTYPE html>
<html>
<head>
    <title>Crypto</title>
</head>
<body style="background-color: black; color: white;">

<?php

// Mapeia as Fan Tokens por causa da diverg�ncia no symbol delas no Mercado Bitcoin
$currencyMapping = array(
    "GALFTUSDT" => "GALUSDT",
    "BARFTUSDT" => "BARUSDT",
    "CITYFTUSDT" => "CITYUSDT",
    "OGFTUSDT" => "OGUSDT",
	"PSGFTUSDT" => "PSGUSDT",
    "ACMFTUSDT" => "ACMUSDT",
	"JUVFTUSDT" => "JUVUSDT",
    "ATMFTUSDT" => "ATMUSDT",
	"INTERFTUSDT" => "INTERUSDT",
    "ASRFTUSDT" => "ASRUSDT",
	"PORFTUSDT" => "PORUSDT",
    "ARGFTUSDT" => "ARGUSDT",
	"UFCFTUSDT" => "UFCUSDT",
    "MENGOFTUSDT" => "MENGOUSDT",
	"SCCPFTUSDT" => "SCCPUSDT",
    "AMFTUSDT" => "AMUSDT",
	"PFLFTUSDT" => "PFLUSDT",
    "SAUBERFTUSDT" => "SAUBERUSDT",
	"GALOFTUSDT" => "GALOUSDT",
    "YBOFTUSDT" => "YBOUSDT",
	"NAVIFTUSDT" => "NAVIUSDT",
    "ALLFTUSDT" => "ALLUSDT",
	"CAIFTUSDT" => "CAIUSDT",
    "STVFTUSDT" => "STVUSDT",
	"THFTUSDT" => "THUSDT",
    "VERDAOFTUSDT" => "VERDAOUSDT",
	
  
    
    "GALUSDT" => "GALFTUSDT",
    "BARUSDT" => "BARFTUSDT",
    "CITYUSDT" => "CITYFTUSDT",
    "OGUSDT" => "OGFTUSDT",
	"PSGUSDT" => "PSGFTUSDT",
    "ACMUSDT" => "ACMFTUSDT",
	"JUVUSDT" => "JUVFTUSDT",
    "ATMUSDT" => "ATMFTUSDT",
	"INTERUSDT" => "INTERFTUSDT",
    "ASRUSDT" => "ASRFTUSDT",
	"PORUSDT" => "PORFTUSDT",
    "ARGUSDT" => "ARGFTUSDT",
	"UFCUSDT" => "UFCFTUSDT",
    "MENGOUSDT" => "MENGOFTUSDT",
	"SCCPUSDT" => "SCCPFTUSDT",
    "AMUSDT" => "AMFTUSDT",
	"PFLUSDT" => "PFLFTUSDT",
    "SAUBERUSDT" => "SAUBERFTUSDT",
	"GALOUSDT" => "GALOFTUSDT",
    "YBOUSDT" => "YBOFTUSDT",
	"NAVIUSDT" => "NAVIFTUSDT",
    "ALLUSDT" => "ALLFTUSDT",
	"CAIUSDT" => "CAIFTUSDT",
    "STVUSDT" => "STVFTUSDT",
	"THUSDT" => "THFTUSDT",
    "VERDAOUSDT" => "VERDAOFTUSDT"
    // Adicionar mais linhas caso necess�rio afim de manter essa lista atualizada
);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crypto";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conex�o falhou: " . $conn->connect_error);
}

$query = "SELECT DISTINCT Moeda FROM `Binance`
    UNION SELECT DISTINCT Moeda FROM `Bybit`
  ##  UNION SELECT DISTINCT Moeda FROM `Poloniex`
    UNION SELECT DISTINCT Moeda FROM `Whitebit`
    UNION SELECT DISTINCT Moeda FROM `Mercado Bitcoin`
    UNION SELECT DISTINCT Moeda FROM `Foxbit`
	UNION SELECT DISTINCT Moeda FROM `Okx`
	UNION SELECT DISTINCT Moeda FROM `Bitso`
	UNION SELECT DISTINCT Moeda FROM `Novadax`
    ";
    
$result = $conn->query($query);

$profits = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $moeda = $row["Moeda"];

 // Dentro do loop de cada criptomoeda:
$moedaWithFT = $moeda . 'FTUSDT';

// Verifica se a moeda tem um mapping e usa o symbol mapeado caso dispon�vel
$moedaToCompare = isset($currencyMapping[$moeda]) ? $currencyMapping[$moeda] : $moeda;

// Monta o buyPricesQuery e o sellPricesQuery considerando que elas podem ou n�o ter FT no nome (por causa da diverg�ncia no nome das FT no Mercado Bitcoin)
$buyPricesQuery = "SELECT Preco, Quantidade, Volume, Corretora FROM (
          SELECT Preco, Quantidade, Volume, 'Binance' AS Corretora FROM `Binance` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'sell' 
 ##	UNION SELECT Preco, Quantidade, Volume, 'Bitso' AS Corretora FROM `Bitso` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'sell'
    UNION SELECT Preco, Quantidade, Volume, 'Bybit' AS Corretora FROM `Bybit` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'sell'
 ## UNION SELECT Preco, Quantidade, Volume, 'Novadax' AS Corretora FROM `Novadax` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'sell'
 ## UNION SELECT Preco, Quantidade, Volume, 'Whitebit' AS Corretora FROM `Whitebit` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'sell'
 ## UNION SELECT Preco, Quantidade, Volume, 'Poloniex' AS Corretora FROM `Poloniex` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'sell'
 ## UNION SELECT Preco, Quantidade, Volume, 'Chiliz' AS Corretora FROM `Chiliz` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'sell'
 ## UNION SELECT Preco, Quantidade, Volume, 'Foxbit' AS Corretora FROM `Foxbit` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'sell'
 ## UNION SELECT Preco, Quantidade, Volume, 'Okx' AS Corretora FROM `Okx` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'sell'
 ## UNION SELECT Preco, Quantidade, Volume, 'Mercado Bitcoin' AS Corretora FROM `Mercado Bitcoin` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'sell'
) AS Compra";
                            
                            
        $buyPricesResult = $conn->query($buyPricesQuery);

        $buyPrices = array();
        while ($buyPricesRow = $buyPricesResult->fetch_assoc()) {
            $buyPrices[] = $buyPricesRow;
        }

        // Buscar pre�os, quantidades e volume de venda entre todas as corretoras
        
$sellPricesQuery = "SELECT Preco, Quantidade, Volume, Corretora FROM (
          SELECT Preco, Quantidade, Volume, 'Binance' AS Corretora FROM `Binance` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'buy'
    UNION SELECT Preco, Quantidade, Volume, 'Bitso' AS Corretora FROM `Bitso` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'buy'
    UNION SELECT Preco, Quantidade, Volume, 'Novadax' AS Corretora FROM `Novadax` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'buy'
    UNION SELECT Preco, Quantidade, Volume, 'Bybit' AS Corretora FROM `Bybit` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'buy'
    UNION SELECT Preco, Quantidade, Volume, 'Whitebit' AS Corretora FROM `Whitebit` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'buy'
 ## UNION SELECT Preco, Quantidade, Volume, 'Poloniex' AS Corretora FROM `Poloniex` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'buy'
 ## UNION SELECT Preco, Quantidade, Volume, 'Chiliz' AS Corretora FROM `Chiliz` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'buy'
    UNION SELECT Preco, Quantidade, Volume, 'Foxbit' AS Corretora FROM `Foxbit` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'buy'
    UNION SELECT Preco, Quantidade, Volume, 'Okx' AS Corretora FROM `Okx` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'buy'
    UNION SELECT Preco, Quantidade, Volume, 'Mercado Bitcoin' AS Corretora FROM `Mercado Bitcoin` WHERE (Moeda = '$moedaToCompare' OR Moeda = '$moedaWithFT') AND Tipo = 'buy'
) AS Venda";
                             
                             
        $sellPricesResult = $conn->query($sellPricesQuery);

        $sellPrices = array();
        while ($sellPricesRow = $sellPricesResult->fetch_assoc()) {
            $sellPrices[] = $sellPricesRow;
        }

        if (!empty($buyPrices) && !empty($sellPrices)) {
            $minBuyPrice = min(array_column($buyPrices, 'Preco'));
            $maxSellPrice = max(array_column($sellPrices, 'Preco'));
            $lucro = (($maxSellPrice - $minBuyPrice) / $minBuyPrice) * 100;
			
// Aqui define a porcentagem de lucro minima para ser exibida no frontend
            if ($lucro > 4) { 
                $lowestBuyExchange = '';
                $highestSellExchange = '';

                foreach ($buyPrices as $buy) {
                    if ($buy['Preco'] == $minBuyPrice) {
                        $lowestBuyExchange = $buy['Corretora'];
                    }
                }

                foreach ($sellPrices as $sell) {
                    if ($sell['Preco'] == $maxSellPrice) {
                        $highestSellExchange = $sell['Corretora'];
                    }
                }

                // Consulta a taxa de saque da corretora onde foi comprada a criptomoeda
                $corretoraCompra = $lowestBuyExchange;
                $taxaQuery = "SELECT Taxa FROM `taxas` WHERE Corretora = '$corretoraCompra' AND Moeda = '$moeda'";
                $taxaResult = $conn->query($taxaQuery);

                $taxa = 0; // Default tax value
                if ($taxaResult->num_rows > 0) {
                    $taxaRow = $taxaResult->fetch_assoc();
                    $taxa = $taxaRow['Taxa'];
                }

                // Calculate the value of Taxa * lowest buy price
                $taxaValue = $taxa  * $minBuyPrice;

                // Store information in the $profits array
                $profits[$moeda] = array(
                    'moeda' => $moeda,
                    'lucro' => $lucro,
                    'taxa' => $taxa,
                    'taxaValue' => $taxaValue,
                    'lowestBuyExchange' => $lowestBuyExchange,
                    'highestSellExchange' => $highestSellExchange,
                    'buyPrices' => $buyPrices,
                    'sellPrices' => $sellPrices
                );
            }
        }
    }

    // Sort the $profits array by profit in descending order
    usort($profits, function($a, $b) {
        return $b['lucro'] <=> $a['lucro'];
    });

    // Exibe os resultados organizados por lucro
    foreach ($profits as $profitInfo) {
        // Extract information from the $profitInfo array
        $moeda = $profitInfo['moeda'];
        $lucro = $profitInfo['lucro'];
        $taxa = $profitInfo['taxa'];
        $taxaValue = $profitInfo['taxaValue'];
        $lowestBuyExchange = $profitInfo['lowestBuyExchange'];
        $highestSellExchange = $profitInfo['highestSellExchange'];
        $buyPrices = $profitInfo['buyPrices'];
        $sellPrices = $profitInfo['sellPrices'];



//////////////////////////////////////////////////////////////////////////////
////////////////////////////// AQUI COMEÇA A UI //////////////////////////////
//////////////////////////////////////////////////////////////////////////////




        echo "<div style='border: 2px solid white; padding: 10px; margin: 10px; display: flex; align-items: center;'>";
		

   // Exibe a COMPRA da Criptomoeda
        echo "<div style='flex: 1; text-align: left;'><strong>Compra (Livro das vendas)</strong><br><br>";

// Encontre o menor preço e a corretora correspondente
$minBuyPrice = PHP_FLOAT_MAX;
$corretoraMenorPreco = '';

foreach ($buyPrices as $buy) {
    if ($buy['Preco'] < $minBuyPrice) {
        $minBuyPrice = $buy['Preco'];
        $corretoraMenorPreco = $buy['Corretora'];
    }
}

// Calcular o preço máximo permitido com base no lucro
$maxAllowedPrice = $minBuyPrice + ($minBuyPrice * $lucro / 100);



foreach ($buyPrices as $buy) {
    if ($buy['Corretora'] === $corretoraMenorPreco && $buy['Preco'] <= $maxAllowedPrice) {
        // Calculate the upper limit for the price based on the lucro
        $upperLimit = $minBuyPrice + ($minBuyPrice * $lucro / 4 / 100);

        // Check if the current buy price is within the specified range
        if ($buy['Preco'] <= $upperLimit) {
            // If it is, display the entire line in green
            $lineText = "<span style='color: green;'>";
        } else {
            // If not, display the line as usual
            $lineText = "";
        }

        // Display the rest of the information
        echo $lineText . "{$buy['Corretora']} - Preco: " . number_format($buy['Preco'], 4) . " / Quantidade: " . number_format($buy['Quantidade'], 4) . " ( USD$ " . number_format($buy['Volume'], 2) . " )</span><br>";
    }
}



// Inserir links "Order Book" e "Saque" para a corretora Binance
if ($corretoraMenorPreco === 'Binance') {
    $moedaWithoutUSDT = substr($moeda, 0, -4);
    $binanceOrderBookLink = "https://www.binance.com/en/trade/{$moedaWithoutUSDT}_USDT?_from=markets&theme=dark&type=spot";
    $binanceWithdrawLink = "https://www.binance.com/en/my/wallet/account/main/withdrawal/crypto/{$moedaWithoutUSDT}";

    echo "<br><button><a href='{$binanceOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$binanceWithdrawLink}' target='_blank'>Saque</a></button><br>";

}

// Inserir links "Order Book" e "Saque" para a corretora Mercado Bitcoin
if ($corretoraMenorPreco === 'Mercado Bitcoin') {
    $moedaWithoutUSDT = substr($moeda, 0, -4);
    $mercadobitcoinOrderBookLink = "https://pro.mercadobitcoin.com.br/pro/painel/{$moedaWithoutUSDT}/brl";
    $mercadobitcoinWithdrawLink = "https://www.mercadobitcoin.com.br/plataforma/transferencia/crypto/{$moedaWithoutUSDT}";

    echo "<br><button><a href='{$mercadobitcoinOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$mercadobitcoinWithdrawLink}' target='_blank'>Saque</a></button><br>";

}

// Inserir links "Order Book" e "Saque" para a corretora Bybit
if ($corretoraMenorPreco === 'Bybit') {
    $moedaWithoutUSDT = substr($moeda, 0, -4);
    $bybitOrderBookLink = "https://www.bybit.com/pt-BR/trade/spot/{$moedaWithoutUSDT}/USDT";
    $bybitWithdrawLink = "https://www.bybit.com/user/assets/home/spot";

    echo "<br><button><a href='{$bybitOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$bybitWithdrawLink}' target='_blank'>Saque</a></button><br>";

}

// Inserir links "Order Book" e "Saque" para a corretora Foxbit
if ($corretoraMenorPreco === 'Foxbit') {
    $moedaInLowerCase = strtolower(substr($moeda, 0, -4));
    $foxbitOrderBookLink = "https://app.foxbit.com.br/terminal/{$moedaInLowerCase}brl";
    $foxbitWithdrawLink = "https://app.foxbit.com.br/wallet/withdraw/{$moedaInLowerCase}";

    echo "<br><button><a href='{$foxbitOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$foxbitWithdrawLink}' target='_blank'>Saque</a></button><br>";

}

// Inserir links "Order Book" e "Saque" para a corretora Okx
if ($corretoraMenorPreco === 'Okx') {
    $moedaInLowerCase = strtolower(substr($moeda, 0, -4));
    $OkxOrderBookLink = "https://www.okx.com/pt-br/trade-spot/{$moedaInLowerCase}-usdt";
    $OkxWithdrawLink = "https://www.okx.com/pt-br/balance/withdrawal/{$moedaInLowerCase}";

    echo "<br><button><a href='{$OkxOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$OkxWithdrawLink}' target='_blank'>Saque</a></button><br>";

}

// Inserir links "Order Book" e "Saque" para a corretora Bitso
if ($corretoraMenorPreco === 'Bitso') {
    $moedaInLowerCase = strtolower(substr($moeda, 0, -4));
    $BitsoOrderBookLink = "https://bitso.com/alpha/{$moedaInLowerCase}/usd";
    $BitsoWithdrawLink = "https://bitso.com/wallet/{$moedaInLowerCase}/withdraw";

    echo "<br><button><a href='{$BitsoOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$BitsoWithdrawLink}' target='_blank'>Saque</a></button><br>";

}

// Inserir links "Order Book" e "Saque" para a corretora Novadax
if ($corretoraMenorPreco === 'Novadax') {
    $moedaWithoutUSDT = substr($moeda, 0, -4);
    $novadaxOrderBookLink = "https://www.novadax.com/en-US/product/orderbook?pair={$moedaWithoutUSDT}_brl&tag=All";
    $novadaxWithdrawLink = "https://www.novadax.com/en-US/account/send/{$moedaWithoutUSDT}";

    echo "<br><button><a href='{$novadaxOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$novadaxWithdrawLink}' target='_blank'>Saque</a></button><br>";

}

// Inserir links "Order Book" e "Saque" para a corretora Whitebit
if ($corretoraMenorPreco === 'Whitebit') {
    $moedaWithoutUSDT = substr($moeda, 0, -4);
    $WhitebitOrderBookLink = "https://whitebit.com/trade/{$moedaWithoutUSDT}-USDT?type=spot&tab=open-orders";
    $WhitebitWithdrawLink = "https://whitebit.com/withdraw?ticker={$moedaWithoutUSDT}";

    echo "<br><button><a href='{$WhitebitOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$WhitebitWithdrawLink}' target='_blank'>Saque</a></button><br>";

}

echo "</div>";

echo "<div style='flex: 1; text-align: center;'><strong>" . substr($moeda, 0, -4) . "</strong><br>";
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
// Aqui é o texto que fica no meio da tela
echo "Lucro: " . number_format($lucro, 2) . "%<br>";
echo "Taxa: " . number_format($taxa, 2) . " " . strtolower(substr($moeda, 0, -4)) . " (USD$ " . number_format($taxaValue, 2) . ")<br>";

// Calcula a Liquidez Mínima
if ($lucro != 0) {
    $liquidezMinima = $taxaValue / ($lucro / 100);
    echo "Liquidez Mínima: USD$ " . number_format($liquidezMinima, 2) . "<br>";
}

echo "</div>";
// ***

		

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Exibe a VENDA da Criptomoeda
		
		
        // Encontre o maior preço e a corretora correspondente
$maxSellPrice = 0;
$corretoraMaiorPreco = '';

foreach ($sellPrices as $sell) {
    if ($sell['Preco'] > $maxSellPrice) {
        $maxSellPrice = $sell['Preco'];
        $corretoraMaiorPreco = $sell['Corretora'];
    }
}

// Calcular o preço mínimo permitido com base no lucro
$minAllowedPrice = $maxSellPrice - ($maxSellPrice * $lucro / 100);

echo "<div style='flex: 1; text-align: left;'><strong>Venda (Livro das compras)</strong><br><br>";

foreach ($sellPrices as $sell) {
    // Displaying only prices from the exchange with the highest price and above the minimum allowed price
    if ($sell['Corretora'] === $corretoraMaiorPreco && $sell['Preco'] >= $minAllowedPrice) {
        // Check if the price is up to 1/3 less than the maximum price based on profit percentage
        $lowerLimit = $maxSellPrice - ($maxSellPrice * $lucro / 4 / 100);

        // Apply green color if the condition is met
        $priceColor = ($sell['Preco'] >= $lowerLimit) ? 'color: green;' : '';

        // Display the information with the applied color
        echo "<span style='{$priceColor}'>{$sell['Corretora']} - Preco: " . number_format($sell['Preco'], 4) . " / Quantidade: " . number_format($sell['Quantidade'], 4) . " ( USD$ " . number_format($sell['Volume'], 2) . " )</span><br>";
    }
}

// Inserir links "Order Book" e "Depósito" para a corretora Binance
if ($corretoraMaiorPreco === 'Binance') {
    $moedaWithoutUSDT = substr($moeda, 0, -4);
    $binanceOrderBookLink = "https://www.binance.com/en/trade/{$moedaWithoutUSDT}_USDT?_from=markets&theme=dark&type=spot";
    $binanceDepositLink = "https://www.binance.com/en/my/wallet/account/main/deposit/crypto/{$moedaWithoutUSDT}";
	
    echo "<br><button><a href='{$binanceOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$binanceDepositLink}' target='_blank'>Depósito</a><br>";

}

// Inserir links "Order Book" e "Depósito" para a corretora Mercado Bitcoin
if ($corretoraMaiorPreco === 'Mercado Bitcoin') {
    $moedaWithoutUSDT = substr($moeda, 0, -4);
    $mercadobitcoinOrderBookLink = "https://pro.mercadobitcoin.com.br/pro/painel/{$moedaWithoutUSDT}/brl";
    $mercadobitcoinDepositLink = "https://www.mercadobitcoin.com.br/plataforma/deposito/crypto/{$moedaWithoutUSDT}";

    echo "<br><button><a href='{$mercadobitcoinOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$mercadobitcoinDepositLink}' target='_blank'>Depósito</a><br>";

}

// Inserir links "Order Book" e "Depósito" para a corretora Bybit
if ($corretoraMaiorPreco === 'Bybit') {
    $moedaWithoutUSDT = substr($moeda, 0, -4);
    $bybitOrderBookLink = "https://www.bybit.com/pt-BR/trade/spot/{$moedaWithoutUSDT}/USDT";
    $bybitDepositLink = "https://www.bybit.com/user/assets/home/spot";

    echo "<br><button><a href='{$bybitOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$bybitDepositLink}' target='_blank'>Depósito</a><br>";

}

// Inserir links "Order Book" e "Depósito" para a corretora Foxbit
if ($corretoraMaiorPreco === 'Foxbit') {
    $moedaInLowerCase = strtolower(substr($moeda, 0, -4));
    $foxbitOrderBookLink = "https://app.foxbit.com.br/terminal/{$moedaInLowerCase}brl";
    $foxbitDepositLink = "https://app.foxbit.com.br/wallet/deposit/{$moedaInLowerCase}";

    echo "<br><button><a href='{$foxbitOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$foxbitDepositLink}' target='_blank'>Depósito</a><br>";

}

// Inserir links "Order Book" e "Depósito" para a corretora Okx
if ($corretoraMaiorPreco === 'Okx') {
    $moedaInLowerCase = strtolower(substr($moeda, 0, -4));
    $OkxOrderBookLink = "https://www.okx.com/pt-br/trade-spot/{$moedaInLowerCase}-usdt";
    $OkxDepositLink = "https://www.okx.com/pt-br/balance/recharge/{$moedaInLowerCase}";

    echo "<br><button><a href='{$OkxOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$OkxDepositLink}' target='_blank'>Depósito</a><br>";

}

// Inserir links "Order Book" e "Depósito" para a corretora Bitso
if ($corretoraMaiorPreco === 'Bitso') {
    $moedaInLowerCase = strtolower(substr($moeda, 0, -4));
    $BitsoOrderBookLink = "https://bitso.com/alpha/{$moedaInLowerCase}/usd";
    $BitsoDepositLink = "https://bitso.com/wallet/{$moedaInLowerCase}/fund";

    echo "<br><button><a href='{$BitsoOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$BitsoDepositLink}' target='_blank'>Depósito</a><br>";

}

// Inserir links "Order Book" e "Depósito" para a corretora Novadax
if ($corretoraMaiorPreco === 'Novadax') {
    $moedaWithoutUSDT = substr($moeda, 0, -4);
    $novadaxOrderBookLink = "https://www.novadax.com/en-US/product/orderbook?pair={$moedaWithoutUSDT}_brl&tag=All";
    $novadaxDepositLink = "https://www.novadax.com/en-US/account/receive/{$moedaWithoutUSDT}";

    echo "<br><button><a href='{$novadaxOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$novadaxDepositLink}' target='_blank'>Depósito</a><br>";

}

// Inserir links "Order Book" e "Depósito" para a corretora Whitebit
if ($corretoraMaiorPreco === 'Whitebit') {
    $moedaWithoutUSDT = substr($moeda, 0, -4);
    $WhitebitOrderBookLink = "https://whitebit.com/trade/{$moedaWithoutUSDT}-USDT?type=spot&tab=open-orders";
    $WhitebitDepositLink = "https://whitebit.com/deposit?ticker={$moedaWithoutUSDT}&method=address";

    echo "<br><button><a href='{$WhitebitOrderBookLink}' target='_blank'>Livro</a></button>  <button><a href='{$WhitebitDepositLink}' target='_blank'>Depósito</a><br>";

}

echo "</div>";


        echo "</div>";

        echo "</div>";
    }
	
	////////////////////////////////
	////////////FIM DA UI//////////
	///////////////////////////////
	
	// E caso n�o encontre nenhuma moeda:	
} else {
    echo "Nenhuma moeda encontrada no banco de dados.";
}
    // The end is near
$conn->close();
?>
</body>
</html>