<?php
// templates/footer.php - Common HTML Footer Template

$appConfig = require __DIR__ . '/../config/app.php';
?>

<!-- Footer content can be added here if needed -->

<?php if (isset($additionalJS)): ?>
    <?= $additionalJS ?>
<?php endif; ?>

</body>
</html> 