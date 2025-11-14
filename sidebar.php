<style>
    .sidebar {
        width: 250px;
        background: #0056b3;
        min-height: 100vh;
        padding-top: 20px;
        color: white;
        box-shadow: 2px 0 6px rgba(0,0,0,0.3);
    }

    .sidebar h3 {
        text-align: center;
        margin-bottom: 20px;
        font-size: 20px;
        letter-spacing: 1px;
    }

    .menu {
        list-style: none;
        padding: 0;
    }

    .menu li {
        padding: 15px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }

    .menu li a {
        color: white;
        text-decoration: none;
        font-size: 16px;
        display: block;
    }

    .menu li:hover {
        background: #003f80;
        cursor: pointer;
    }
</style>

<div class="sidebar">
    <h3>MENU</h3>

    <ul class="menu">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="informasi_ska.php">Informasi SKA</a></li>
        <li><a href="survei.php">Form Survei</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
