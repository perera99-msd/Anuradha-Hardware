<!-- includes/header.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/adminstyle.css">


    <style>
        body {
            margin: 0;
        }

        #sidebar {
            transition: all 0.3s ease;
        }

        #sidebar.collapsed {
            width: 70px !important;
        }

        #sidebar.collapsed span {
            display: none !important;
        }
    </style>
</head>

<body>

    <!-- Top navbar -->
    <nav class="navbar navbar-dark bg-dark px-3">
        <button class="btn btn-outline-light" id="toggleSidebar">
            <i class="bi bi-list"></i>
        </button>

        <form class="d-flex ms-auto me-3">
            <!-- <input class="form-control me-2" type="search" placeholder="Search..." aria-label="Search"> -->
        </form>

        <div class="text-white">Admin</div>
    </nav>