param(
    [Parameter(Mandatory = $true)]
    [string]$MetadataFile
)

$ErrorActionPreference = 'Stop'
$today = Get-Date -Format 'yyyy.MM.dd'
$sqlDate = Get-Date -Format 'yyyy-MM-dd'
$currentVersion = $null

if (Test-Path -LiteralPath $MetadataFile) {
    $content = Get-Content -LiteralPath $MetadataFile -Raw
    if ($content -match "'text_version', 'TCSB-(\d{4})\.(\d{2})\.(\d{2})\.(\d+)'") {
        $currentVersion = "TCSB-$($Matches[1]).$($Matches[2]).$($Matches[3]).$($Matches[4])"
        $currentDate = "$($Matches[1]).$($Matches[2]).$($Matches[3])"
        $currentSeq = [int]$Matches[4]
    }
}

if ($currentVersion -and $currentDate -eq $today) {
    $nextSeq = $currentSeq + 1
} else {
    $nextSeq = 1
}

$nextVersion = "TCSB-$today.$nextSeq"
$sql = @"
DROP TABLE IF EXISTS ``tcsb_text_metadata``;
CREATE TABLE ``tcsb_text_metadata`` (
  ``metadataKey`` varchar(40) NOT NULL,
  ``metadataValue`` varchar(255) NOT NULL,
  PRIMARY KEY (``metadataKey``)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ``tcsb_text_metadata`` (``metadataKey``, ``metadataValue``) VALUES ('text_version', '$nextVersion');
INSERT INTO ``tcsb_text_metadata`` (``metadataKey``, ``metadataValue``) VALUES ('version_date', '$sqlDate');
INSERT INTO ``tcsb_text_metadata`` (``metadataKey``, ``metadataValue``) VALUES ('version_source', 'bookish-lamp/database/bibleVerses.sql');
"@

Set-Content -LiteralPath $MetadataFile -Value $sql -Encoding UTF8
Write-Output $nextVersion
