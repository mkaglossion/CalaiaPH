param(
    [string]$DumpPath = '',

    [string]$SourceDatabase = 'calaiaph',

    [string]$TargetDatabase = '',

    [string]$RestoreDatabase = '',

    [string]$DbHost = '127.0.0.1',

    [int]$Port = 3306,

    [string]$User = 'root',

    [string]$Password = '',

    [switch]$Force,

    [string]$PhpMyAdminBaseUrl = 'http://localhost/phpmyadmin6/public/index.php?route=/database/import&db='
)

$ErrorActionPreference = 'Stop'

function Get-MySqlDumpClient {
    $command = Get-Command mysqldump -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    $fallbackRoots = @(
        'C:\laragon\bin\mysql',
        'C:\xampp\mysql\bin',
        'C:\Program Files\MariaDB',
        'C:\Program Files (x86)\MariaDB'
    )

    foreach ($root in $fallbackRoots) {
        if (-not (Test-Path $root)) {
            continue
        }

        $match = Get-ChildItem -Path $root -Recurse -File -Filter 'mysqldump.exe' -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($match) {
            return $match.FullName
        }
    }

    throw "Could not find mysqldump.exe. Install MariaDB/MySQL client tools or add mysqldump.exe to PATH."
}

function Get-MySqlClient {
    $command = Get-Command mysql -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    $fallbackRoots = @(
        'C:\laragon\bin\mysql',
        'C:\xampp\mysql\bin',
        'C:\Program Files\MariaDB',
        'C:\Program Files (x86)\MariaDB'
    )

    foreach ($root in $fallbackRoots) {
        if (-not (Test-Path $root)) {
            continue
        }

        $match = Get-ChildItem -Path $root -Recurse -File -Filter 'mysql.exe' -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($match) {
            return $match.FullName
        }
    }

    throw "Could not find mysql.exe. Install MariaDB/MySQL client tools or add mysql.exe to PATH."
}

function New-RestoreDatabaseName {
    $stamp = Get-Date -Format 'yyyyMMdd_HHmmss'
    return "calaiaph_restore_$stamp"
}

function Invoke-DatabaseBackup {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Database,

        [Parameter(Mandatory = $true)]
        [string]$DbHost,

        [Parameter(Mandatory = $true)]
        [int]$Port,

        [Parameter(Mandatory = $true)]
        [string]$User,

        [string]$Password = '',

        [Parameter(Mandatory = $true)]
        [string]$OutputDirectory
    )

    $mysqlDump = Get-MySqlDumpClient

    if (-not (Test-Path -LiteralPath $OutputDirectory)) {
        New-Item -ItemType Directory -Path $OutputDirectory -Force | Out-Null
    }

    $timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
    $safeDatabase = ($Database -replace '[^a-zA-Z0-9_-]', '_')
    $backupPath = Join-Path $OutputDirectory "$safeDatabase-$timestamp.sql"

    $args = @(
        '--host', $DbHost,
        '--port', $Port,
        '--user', $User,
        '--single-transaction',
        '--skip-lock-tables',
        '--routines',
        '--triggers',
        '--events',
        '--hex-blob',
        '--default-character-set=utf8mb4',
        $Database
    )

    if (-not [string]::IsNullOrWhiteSpace($Password)) {
        $args = @('--password=' + $Password) + $args
    }

    Write-Host "Backing up database: $Database"
    Write-Host "Output file: $backupPath"

    $process = Start-Process -FilePath $mysqlDump -ArgumentList $args -NoNewWindow -PassThru -Wait -RedirectStandardOutput $backupPath

    if ($process.ExitCode -ne 0) {
        throw "Backup failed."
    }

    Write-Host "Backup complete."

    return $backupPath
}

function Invoke-DatabaseImport {
    param(
        [Parameter(Mandatory = $true)]
        [string]$DumpPath,

        [Parameter(Mandatory = $true)]
        [string]$TargetDatabase,

        [Parameter(Mandatory = $true)]
        [string]$DbHost,

        [Parameter(Mandatory = $true)]
        [int]$Port,

        [Parameter(Mandatory = $true)]
        [string]$User,

        [string]$Password = ''
    )

    $mysql = Get-MySqlClient

    $connectionArgs = @(
        '--host', $DbHost,
        '--port', $Port,
        '--user', $User
    )

    if (-not [string]::IsNullOrWhiteSpace($Password)) {
        $connectionArgs += "--password=$Password"
    }

    Write-Host "Creating database: $TargetDatabase"
    $createDatabaseSql = "CREATE DATABASE IF NOT EXISTS $TargetDatabase CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    & $mysql @connectionArgs -e $createDatabaseSql

    if ($LASTEXITCODE -ne 0) {
        throw "Failed to create database $TargetDatabase."
    }

    Write-Host "Importing dump into: $TargetDatabase"
    $importArgs = @($connectionArgs + @($TargetDatabase))

    Write-Host "Using mysql client: $mysql"
    Get-Content -LiteralPath $DumpPath -Encoding utf8 | & $mysql @importArgs

    if ($LASTEXITCODE -ne 0) {
        throw "Import failed. The source database was not modified."
    }

    Write-Host "Done. Your existing database was not touched."
    Write-Host "Imported copy: $TargetDatabase"
}

function Get-DatabaseTableCount {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Database,

        [Parameter(Mandatory = $true)]
        [string]$DbHost,

        [Parameter(Mandatory = $true)]
        [int]$Port,

        [Parameter(Mandatory = $true)]
        [string]$User,

        [string]$Password = ''
    )

    $mysql = Get-MySqlClient

    $connectionArgs = @(
        '--host', $DbHost,
        '--port', $Port,
        '--user', $User
    )

    if (-not [string]::IsNullOrWhiteSpace($Password)) {
        $connectionArgs += "--password=$Password"
    }

    $tableCountSql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$Database';"

    Write-Host "Using mysql client: $mysql"
    $tableCount = & $mysql @connectionArgs -N -B -e $tableCountSql

    if ($LASTEXITCODE -ne 0) {
        throw "Failed to count tables."
    }

    return (($tableCount | Out-String).Trim())
}

function Test-DatabaseExists {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Database,

        [Parameter(Mandatory = $true)]
        [string]$DbHost,

        [Parameter(Mandatory = $true)]
        [int]$Port,

        [Parameter(Mandatory = $true)]
        [string]$User,

        [string]$Password = ''
    )

    $mysql = Get-MySqlClient

    $connectionArgs = @(
        '--host', $DbHost,
        '--port', $Port,
        '--user', $User
    )

    if (-not [string]::IsNullOrWhiteSpace($Password)) {
        $connectionArgs += "--password=$Password"
    }

    $existsSql = "SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name = '$Database';"

    Write-Host "Using mysql client: $mysql"
    $schemaExists = & $mysql @connectionArgs -N -B -e $existsSql

    if ($LASTEXITCODE -ne 0) {
        throw "Failed to verify database existence."
    }

    $schemaExistsValue = (($schemaExists | Out-String).Trim())

    if ([string]::IsNullOrWhiteSpace($schemaExistsValue) -or $schemaExistsValue -eq '0') {
        throw "Database not found: $Database"
    }
}

if ($DumpPath -and $RestoreDatabase) {
    throw "Use either -DumpPath or -RestoreDatabase, not both."
}

if ($DumpPath) {
    if (-not (Test-Path -LiteralPath $DumpPath)) {
        throw "Dump file not found: $DumpPath"
    }

    if ([string]::IsNullOrWhiteSpace($TargetDatabase)) {
        $TargetDatabase = New-RestoreDatabaseName
    }

    if ($TargetDatabase -notmatch '^[A-Za-z0-9_]+$') {
        throw "TargetDatabase must contain only letters, numbers, and underscores."
    }

    $backupOutputDirectory = Join-Path $PSScriptRoot '..\storage\app\db-backups'
    Invoke-DatabaseBackup -Database $SourceDatabase -DbHost $DbHost -Port $Port -User $User -Password $Password -OutputDirectory $backupOutputDirectory | Out-Null

    Write-Host ""
    Write-Host "About to import the dump into a new database copy:"
    Write-Host "  Source backup: $SourceDatabase"
    Write-Host "  Dump file:     $DumpPath"
    Write-Host "  Target DB:     $TargetDatabase"

    if (-not $Force) {
        $confirm = Read-Host "Continue? Type YES to proceed"

        if ($confirm -ne 'YES') {
            Write-Host "Import cancelled. Backup is kept, live database was not touched."
            return
        }
    }

    Invoke-DatabaseImport -DumpPath $DumpPath -TargetDatabase $TargetDatabase -DbHost $DbHost -Port $Port -User $User -Password $Password

    Write-Host "Step 3: Verifying restored database: $TargetDatabase"
    $tableCountValue = Get-DatabaseTableCount -Database $TargetDatabase -DbHost $DbHost -Port $Port -User $User -Password $Password
    Write-Host "table_count"
    Write-Host $tableCountValue
    Write-Host "All steps complete."
    Write-Host "Restore database ready: $TargetDatabase"
    Write-Host "phpMyAdmin import URL: $PhpMyAdminBaseUrl$TargetDatabase"
    return
}

if ($RestoreDatabase) {
    if ($RestoreDatabase -notmatch '^[A-Za-z0-9_]+$') {
        throw "RestoreDatabase must contain only letters, numbers, and underscores."
    }

    Test-DatabaseExists -Database $RestoreDatabase -DbHost $DbHost -Port $Port -User $User -Password $Password
    $tableCountValue = Get-DatabaseTableCount -Database $RestoreDatabase -DbHost $DbHost -Port $Port -User $User -Password $Password
    Write-Host "Restore database: $RestoreDatabase"
    Write-Host "Table count: $tableCountValue"
    Write-Host "phpMyAdmin import URL: $PhpMyAdminBaseUrl$RestoreDatabase"
    return
}

$backupOutputDirectory = Join-Path $PSScriptRoot '..\storage\app\db-backups'
Invoke-DatabaseBackup -Database $SourceDatabase -DbHost $DbHost -Port $Port -User $User -Password $Password -OutputDirectory $backupOutputDirectory | Out-Null
