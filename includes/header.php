<!-- 
    ============================================================================
    SQUAD 1: HEADER & NAVIGATION
    ============================================================================
    Dynamic navigation that changes based on user role
    - Guests: Home, Login, Register
    - Members: Dashboard, Book Lab, My Bookings, Profile, Logout
    - Admins: Admin Dashboard, Manage Labs, Users, Reports, Logout
-->

<header>
    <div class="container">
        <nav>
            <!-- Logo -->
            <a href="/lab-booking-system/" class="logo">🔬 LabBook</a>
            
            <!-- Mobile Menu Button -->
            <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
                ☰
            </button>
            
            <!-- Navigation Links -->
            <ul class="nav-links" id="nav-links">
                <?php if (isset($_SESSION['user_id']) && isLoggedIn()): ?>
                    
                    <?php if (isAdmin()): ?>
                        <!-- Admin Navigation -->
                        <li><a href="/lab-booking-system/admin/dashboard.php">📊 Dashboard</a></li>
                        <li><a href="/lab-booking-system/admin/manage_labs.php">🔬 Manage Labs</a></li>
                        <li><a href="/lab-booking-system/admin/manage_users.php">👥 Users</a></li>
                        <li><a href="/lab-booking-system/admin/reports.php">📈 Reports</a></li>
                        
                    <?php else: ?>
                        <!-- Member Navigation -->
                        <li><a href="/lab-booking-system/member/dashboard.php">📊 Dashboard</a></li>
                        <li><a href="/lab-booking-system/member/book.php">🔬 Book Lab</a></li>
                        <li><a href="/lab-booking-system/member/history.php">📋 My Bookings</a></li>
                        <li><a href="/lab-booking-system/member/profile.php">👤 Profile</a></li>
                    <?php endif; ?>
                    
                    <!-- User Info & Logout (Both Admin and Member) -->
                    <li>
                        <span style="color: var(--primary-color); font-weight: 600;">
                            👋 <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                        </span>
                    </li>
                    <li>
                        <a href="/lab-booking-system/auth/logout.php" style="color: var(--danger-color);">
                            🚪 Logout
                        </a>
                    </li>
                    
                <?php else: ?>
                    <!-- Guest Navigation -->
                    <li><a href="/lab-booking-system/">🏠 Home</a></li>
                    <li><a href="/lab-booking-system/auth/login.php">🔒 Login</a></li>
                    <li><a href="/lab-booking-system/auth/register.php">✨ Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<script>
// Mobile menu toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const navLinks = document.getElementById('nav-links');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            
            // Change icon
            if (navLinks.classList.contains('active')) {
                this.textContent = '✕';
            } else {
                this.textContent = '☰';
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('nav')) {
                navLinks.classList.remove('active');
                mobileMenuBtn.textContent = '☰';
            }
        });
    }
});
</script>