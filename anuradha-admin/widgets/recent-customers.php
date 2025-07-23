<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">Recent Customers</div>
    <ul class="list-group list-group-flush">
        <?php
        $customers = mysqli_query($conn, "
            SELECT name, email FROM customers ORDER BY id DESC LIMIT 5
        ");
        while ($row = mysqli_fetch_assoc($customers)) {
            echo "<li class='list-group-item'>
                    <strong>{$row['name']}</strong><br>
                    <small>{$row['email']}</small>
                  </li>";
        }
        ?>
    </ul>
</div>