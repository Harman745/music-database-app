<?php
/**
 * Header Include File
 * Common navigation and header for all pages
 */
?>
<header class="main-header">
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">🎵 Music Database</a>
            </div>
            
            <button class="hamburger" id="hamburger" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <ul class="nav-menu" id="navMenu">
                <li><a href="index.php">Home</a></li>
                <li><a href="albums.php">Albums</a></li>
                <li><a href="artists.php">Artists</a></li>
                <li><a href="search.php">Search</a></li>
                
                <?php if (isset($_SESSION['username'])): ?>
                    <li><a href="library.php">My Library</a></li>
                    <li><a href="achievements.php">Achievements</a></li>
                    <li class="user-menu">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="logout.php" class="btn-logout">Logout</a>
                    </li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php" class="btn-register">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>
