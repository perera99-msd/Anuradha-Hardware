<div class="card shadow-sm">
    <div class="card-header bg-secondary text-white">Recent Orders</div>
    <ul class="list-group list-group-flush">
        <?php
        $result = mysqli_query($conn, "
            SELECT o.id, o.total_amount, o.created_at, c.name 
            FROM orders o 
            JOIN customers c ON o.customer_id = c.id 
            ORDER BY o.created_at DESC 
            LIMIT 6
        ");
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<li class='list-group-item d-flex justify-content-between'>
                    <span><strong>{$row['name']}</strong> - #" . $row['id'] . "</span>
                    <span>Rs. " . number_format($row['total_amount']) . "</span>
                  </li>";
        }
        ?>
    </ul>
</div>