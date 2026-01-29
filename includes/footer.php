<?php
/**
 * Footer Include File
 * Common footer for all pages
 */
?>
<footer class="main-footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-brand">
                <h3>🎵 Music Database</h3>
            </div>
            <div class="footer-links">
                <div class="footer-column">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#">About</a></li>
                        <li><a href="#">Jobs</a></li>
                        <li><a href="#">For the Record</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Communities</h4>
                    <ul>
                        <li><a href="#">For Artists</a></li>
                        <li><a href="#">Developers</a></li>
                        <li><a href="#">Advertising</a></li>
                        <li><a href="#">Investors</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Useful Links</h4>
                    <ul>
                        <li><a href="#">Support</a></li>
                        <li><a href="#">Web Player</a></li>
                        <li><a href="#">Free Mobile App</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-social">
                <a href="#" aria-label="Instagram" class="social-icon">📷</a>
                <a href="#" aria-label="Twitter" class="social-icon">🐦</a>
                <a href="#" aria-label="Facebook" class="social-icon">📘</a>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-legal">
                <a href="#">Legal</a>
                <a href="#">Privacy Center</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Cookies</a>
                <a href="#">About Ads</a>
            </div>
            <div class="footer-copyright">
                <p>© <?php echo date('Y'); ?> Music Database</p>
            </div>
        </div>
    </div>
</footer>

<script>
    // Hamburger menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('navMenu');
        
        if (hamburger) {
            hamburger.addEventListener('click', function() {
                hamburger.classList.toggle('active');
                navMenu.classList.toggle('active');
                document.body.classList.toggle('menu-open');
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                    hamburger.classList.remove('active');
                    navMenu.classList.remove('active');
                    document.body.classList.remove('menu-open');
                }
            });
        }
    });
</script>
