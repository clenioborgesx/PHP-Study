<?php

function lerVcards($caminhoPasta) {
    // Função pra renomear quando estiver criptografado
    function decodificar($str) {
        return quoted_printable_decode($str);//função decodificar embutida no PHP RCF2045
    }

    // Obter uma lista de arquivos vCard (.vcf) na pasta especificada
    $vcards = glob("$caminhoPasta/*.vcf");//função glob inclusa no php para encontrar caminhos no arquivo

    // Iniciar um vetor para guardar os contatos
    $contatos = array();

    // Iterar sobre cada arquivo vCard
    foreach ($vcards as $vcfFile) {//foreach função para iterar dentro do vetor ou array
        // Ler o conteúdo do arquivo vCard
        $conteudo = file_get_contents($vcfFile);//file_get_contents Retorna todo conteudo do arq como string

        // Extrair todas as entradas de vCard no conteúdo
        preg_match_all('/BEGIN:VCARD(.*?)END:VCARD/s', $conteudo, $contem); // preg_match_all encontra os padroes de uma string e salva esses padrões num vetor

        // Iterar sobre cada entrada de vCard encontrada
        foreach ($contem[0] as $vcardContent) {
            // Extrair o nome (FN) do vCard
            preg_match('/FN.*?:(.*)/', $vcardContent, $contemNome);
            $nome = isset($contemNome[1]) ? trim($contemNome[1]) : '';//isset verifica se a var está definida e se não é nula

            // Decodificar o nome se estiver no formato Quoted-Printable
            $nome = decodificar($nome);

            // Extrair todos os números de telefone do vCard
            preg_match_all('/TEL.*?:(.*)/', $vcardContent, $contemTelefones);
            $telefones = isset($contemTelefones[1]) ? $contemTelefones[1] : [];

            // Adicionar cada contato ao vetor de contatos
            foreach ($telefones as $telefone) {
                $contatos[] = array('nome' => $nome, 'telefone' => $telefone);
            }
        }
    }

    // Implementação para ordenar os contatos em ordem alfabética
    usort($contatos, function($a, $b) {//Funcao pronta em PHP pra comparar e ordenar vetores
        return strcmp($a['nome'], $b['nome']);// strcmp compara e retorna as str ordenadas
    });

    // Criando a tabela em HTML
    $html = '<table border="1">';
    $html .= '<tr><th>Nome</th><th>Número de Telefone</th></tr>';

    // Iterar sobre cada contato ordenado
    foreach ($contatos as $contato) {
        $html .= "<tr><td>{$contato['nome']}</td><td>{$contato['telefone']}</td></tr>";
    }

    // Fechar a tabela HTML
    $html .= '</table>';

    //Implementação para criar um Vcard novo após ser organizado em ordem alfabetica
    // Salvar um novo arquivo vCard com a lista ordenada
    $conteudoOrdenado = '';
    foreach ($contatos as $contato) {
        $conteudoOrdenado .= "BEGIN:VCARD\r\n";
        $conteudoOrdenado .= "VERSION:2.1\r\n";
        $conteudoOrdenado .= "FN:{$contato['nome']}\r\n";
        $conteudoOrdenado .= "TEL;CELL:{$contato['telefone']}\r\n";
        $conteudoOrdenado .= "END:VCARD\r\n";
    }
    $caminhoArquivoOrdenado = $caminhoPasta . '/contatos_ordenados.vcf';
    file_put_contents($caminhoArquivoOrdenado, $conteudoOrdenado);

    // Retornar o HTML gerado
    return $html;
}

// Caminho para a pasta que contém os arquivos vCard
$caminhoPastaVcards = 'C:\xampp\htdocs\Desafio\vcards';// preciso aprender a deixar o destino nesse formato => ..\vcards

// Chamar a função para ler os arquivos vCard na pasta especificada e obter o HTML gerado
$htmlTabela = lerVcards($caminhoPastaVcards);

// Exibir a tabela HTML
echo $htmlTabela;
?>
