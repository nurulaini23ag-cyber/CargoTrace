# CargoTrace Daily Automated Git Sync & Submit Script
# Automatically stages, commits, and pushes changes to GitHub

$repoPath = "C:\Users\nurul\.gemini\antigravity\scratch\cargotrace"
$logFile = "$repoPath\storage\logs\git_sync.log"

Set-Location $repoPath

$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"

# Ensure Git Author Identity
git config user.name "nurulaini23ag-cyber"
git config user.email "nurulaini23ag@gmail.com"

# Check for modified or untracked files
$status = git status --porcelain

if ($status) {
    Add-Content -Path $logFile -Value "[$timestamp] Perubahan terdeteksi. Melakukan git add & commit..."
    git add -A
    $commitMsg = "chore(auto-sync): daily automatic sync $timestamp [skip ci]"
    git commit -m $commitMsg
    
    Add-Content -Path $logFile -Value "[$timestamp] Melakukan git push origin main..."
    $pushOutput = git push origin main 2>&1
    Add-Content -Path $logFile -Value "[$timestamp] Hasil push: $pushOutput"
} else {
    # Check if there are unpushed commits
    $unpushed = git log origin/main..HEAD --oneline 2>&1
    if ($unpushed) {
        Add-Content -Path $logFile -Value "[$timestamp] Ada commit lokal yang belum di-push. Melakukan push..."
        $pushOutput = git push origin main 2>&1
        Add-Content -Path $logFile -Value "[$timestamp] Hasil push: $pushOutput"
    } else {
        Add-Content -Path $logFile -Value "[$timestamp] Repository sudah sinkron dengan GitHub (tidak ada perubahan baru)."
    }
}
