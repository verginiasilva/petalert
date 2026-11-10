$envFile = Get-Content .env
$token = ''
foreach ($line in $envFile) {
    if ($line -match '^GITHUB_TOKEN=(.*)$') {
        $token = $matches[1].Trim()
    }
}

if (-not $token) {
    Write-Output "Token não encontrado no .env"
    exit 1
}

# Remove diretório .git antigo com histórico comprometido
if (Test-Path .git) {
    Remove-Item -Path .git -Recurse -Force
}

# Inicializa git novo e limpo
git init -b main
git config user.name "verginia"
git config user.email "verginiadossantossilva14@gmail.com"

# Adiciona arquivos respeitando o .gitignore (.env e quarentena não entram)
git add .
git commit -m "Initial commit: PetAlert - Plataforma Oficial de Adoção, Doação e Proteção Animal"

# Configura remote com token para push autenticado
$remoteUrl = "https://verginiasilva:$token@github.com/verginiasilva/petalert.git"
$cleanRemoteUrl = "https://github.com/verginiasilva/petalert.git"

git remote add origin $remoteUrl
Write-Output "Enviando arquivos limpos para o GitHub..."
git push -u origin main --force

# Remove o token da URL do remote local
git remote set-url origin $cleanRemoteUrl
Write-Output "Push concluído com sucesso! Remote configurado: $cleanRemoteUrl"
