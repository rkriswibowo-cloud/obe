<?php
$content = file_get_contents('RPS PSSI Teori Rev 3.0.pdf');
preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $content, $matches);

$allText = "";
foreach ($matches[1] as $idx => $stream) {
    $decompressed = @gzuncompress($stream);
    if ($decompressed !== false) {
        // Extract strings within parentheses e.g. (Text) Tj or [ (Text1) 120 (Text2) ] TJ
        preg_match_all('/\((.*?)\)\s*Tj/s', $decompressed, $tjMatches);
        if (!empty($tjMatches[1])) {
            $allText .= "\n--- STREAM $idx ---\n" . implode("\n", $tjMatches[1]);
        }

        preg_match_all('/\[(.*?)\]\s*TJ/s', $decompressed, $tjListMatches);
        if (!empty($tjListMatches[1])) {
            foreach ($tjListMatches[1] as $list) {
                preg_match_all('/\((.*?)\)/s', $list, $subMatches);
                if (!empty($subMatches[1])) {
                    $allText .= implode("", $subMatches[1]) . "\n";
                }
            }
        }
    }
}

file_put_contents('scratch_pdf_extracted.txt', $allText);
echo "Extracted " . strlen($allText) . " chars of text to scratch_pdf_extracted.txt\n";
echo substr($allText, 0, 3000);
