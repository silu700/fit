<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fit System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <style>
        body { background: #f4f7f6; }
        #wrapper { display: flex; min-height: 100vh; }
        #sidebar { width: 250px; background: #2c3e50; color: white; min-height: 100vh; }
        #content { flex: 1; padding: 20px; }
        .nav-link { color: rgba(255,255,255,.8); }
        .nav-link:hover { color: white; background: #34495e; }
    </style>
</head>
<body>
<div id="wrapper">
    <nav id="sidebar" class="p-3">
        <h4>FitSystem</h4>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="/index.php"><i class="fas fa-home me-2"></i>Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="/users/list.php"><i class="fas fa-users me-2"></i>Użytkownicy</a></li>
            <li class="nav-item"><a class="nav-link" href="/payments/list.php"><i class="fas fa-wallet me-2"></i>Płatności</a></li>
            <li class="nav-item"><a class="nav-link" href="/plans/index.php"><i class="fas fa-dumbbell me-2"></i>Plany</a></li>
        </ul>
    </nav>
    <div id="content">