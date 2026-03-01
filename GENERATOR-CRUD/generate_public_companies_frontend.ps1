$srcDir = "c:\Users\Lenovo\Documents\PROYECTOS\AQUASHIELD-CRM\resources\js\pages\insurance-companies"
$destDir = "c:\Users\Lenovo\Documents\PROYECTOS\AQUASHIELD-CRM\resources\js\pages\public-companies"

if (Test-Path $destDir) {
    Remove-Item -Recurse -Force $destDir
}
Copy-Item -Path $srcDir -Destination $destDir -Recurse

function Rename-Recursive($path) {
    $items = Get-ChildItem -Path $path | Sort-Object -Property @{Expression={$_.FullName.Length}; Descending=$true}
    foreach ($item in $items) {
        $newName = $item.Name -replace 'Insurance', 'Public'
        $newName = $newName -replace 'insurance', 'public'
        if ($newName -cne $item.Name) {
            Rename-Item -Path $item.FullName -NewName $newName
        }
    }
    
    $items = Get-ChildItem -Path $path
    foreach ($item in $items) {
        if ($item.PSIsContainer) {
            Rename-Recursive -path $item.FullName
        }
    }
}
Rename-Recursive -path $destDir

$files = Get-ChildItem -Path $destDir -Recurse -File
foreach ($file in $files) {
    $content = Get-Content -Path $file.FullName -Raw
    $newContent = $content -replace 'InsuranceCompany', 'PublicCompany'
    $newContent = $newContent -replace 'InsuranceCompanies', 'PublicCompanies'
    $newContent = $newContent -replace 'insurance_company', 'public_company'
    $newContent = $newContent -replace 'insurance_companies', 'public_companies'
    $newContent = $newContent -replace 'insurance-company', 'public-company'
    $newContent = $newContent -replace 'insurance-companies', 'public-companies'
    $newContent = $newContent -replace 'Insurance', 'Public'
    $newContent = $newContent -replace 'insurance', 'public'
    if ($content -cne $newContent) {
        [System.IO.File]::WriteAllText($file.FullName, $newContent)
    }
}
Write-Host "Done templating public-companies frontend"
