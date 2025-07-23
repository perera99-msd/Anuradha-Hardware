<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">Top Selling Products</div>
    <ul class="list-group list-group-flush">
        <?php
        $top = mysqli_query($conn, "
            SELECT p.name, SUM(oi.quantity) as total_sold 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            GROUP BY oi.product_id 
            ORDER BY total_sold DESC 
            LIMIT 6
        ");
        while ($row = mysqli_fetch_assoc($top)) {
            echo "<li class='list-group-item d-flex justify-content-between'>
                    <span>{$row['name']}</span>
                    <span>{$row['total_sold']} sold</span>
                  </li>";
        }
        ?>
    </ul>
</div>