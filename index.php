<?php
require_once 'config/db.php';

// Pobieranie statystyk do boksów
$countUsers = $pdo->query("SELECT COUNT(*) FROM fit_users WHERE subscription_status = 'active'")->fetchColumn();
$countGroups = $pdo->query("SELECT COUNT(*) FROM fit_groups")->fetchColumn();
$countExercises = $pdo->query("SELECT COUNT(*) FROM fit_exercises")->fetchColumn();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pulpit Sterowniczy</h1>
        <a href="users/add.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Nowy Klubowicz
        </a>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-primary border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Aktywni Klubowicze</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $countUsers; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-success border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Grupy (Sloty)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $countGroups; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-info border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Biblioteka Ćwiczeń</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $countExercises; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dumbbell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-warning border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Nieopłacone (Msc)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">5</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Kto dzisiaj trenuje? (Widok Grupowy)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Godzina</th>
                                    <th>Grupa</th>
                                    <th>Osoba</th>
                                    <th>Status Planu</th>
                                    <th>Akcja</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>10:00</td>
                                    <td>G1</td>
                                    <td>Jan Kowalski</td>
                                    <td><span class="badge bg-success">Plan aktywny</span></td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary">Zobacz plan</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>