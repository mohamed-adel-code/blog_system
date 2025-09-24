<?php
// Ensure BASE_URL and $lang are defined (assumed from config.php and language file)
?>

</div><!-- /container -->

<!-- Footer section -->
<footer class="bg-dark text-white text-center py-3 mt-4">
    <div class="container">
        <!-- Copyright notice with dynamic year and blog title -->
        <p>&copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars($lang['blog_title'] ?? 'My Blog'); ?>. <?php echo htmlspecialchars($lang['all_rights_reserved'] ?? 'All Rights Reserved'); ?></p>
    </div>
</footer>

<!-- Load JavaScript dependencies -->
<script src="<?php echo BASE_URL; ?>/public/assets/js/jquery-3.7.1.slim.min.js"></script>
<script src="<?php echo BASE_URL; ?>/public/assets/js/popper.min.js"></script>
<script src="<?php echo BASE_URL; ?>/public/assets/js/bootstrap.min.js"></script>
</body>

</html>