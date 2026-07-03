# Build WordPress.org release ZIP with forward-slash paths (Linux-safe).
# Usage: powershell -File scripts/build-release-zip.ps1

$ErrorActionPreference = 'Stop'
$pluginRoot = Split-Path -Parent $PSScriptRoot
$slug = 'vms-elements-fastspring-payment-gateway'
$zipPath = Join-Path $pluginRoot "dist\$slug.zip"
$exclude = @('docs', 'wordpress-org-assets', 'dist', 'scripts', '.git', 'README.md', 'gitignore.example', 'distignore.example', '.distignore')
$stagingParent = Join-Path $env:TEMP 'vms-efpg-zipbuild'
$dest = Join-Path $stagingParent $slug

if (Test-Path $stagingParent) {
	Remove-Item $stagingParent -Recurse -Force
}
New-Item -ItemType Directory -Path $dest -Force | Out-Null

Get-ChildItem $pluginRoot -Force | Where-Object { $exclude -notcontains $_.Name } | ForEach-Object {
	Copy-Item $_.FullName -Destination $dest -Recurse -Force
}

$distDir = Join-Path $pluginRoot 'dist'
if (-not (Test-Path $distDir)) {
	New-Item -ItemType Directory -Path $distDir -Force | Out-Null
}
if (Test-Path $zipPath) {
	Remove-Item $zipPath -Force
}

tar -a -cf $zipPath -C $stagingParent $slug
Remove-Item $stagingParent -Recurse -Force

$item = Get-Item $zipPath
Write-Host "Built: $($item.FullName)"
Write-Host "Size:  $($item.Length) bytes"
