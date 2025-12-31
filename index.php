<?php
/**
 * ============================================================================
 * SQUAD 1: LANDING PAGE (index.php)
 * ============================================================================
 * The "Front Door" - First impression of the website
 * Dynamic content based on user login status and role
 */

session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Get some stats for the hero section (with error handling)
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM labs WHERE status = 'active'");
    $totalLabs = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'member'");
    $totalUsers = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $totalBookings = $stmt->fetch()['total'];
} catch (PDOException $e) {
    // Fallback values if database query fails
    $totalLabs = 5;
    $totalUsers = 0;
    $totalBookings = 0;
    logError('Stats query error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Booking System - Book Labs Online</title>
    <meta name="description" content="Book laboratory sessions quickly and efficiently with our modern lab booking system">
    <meta name="keywords" content="lab booking, laboratory, booking system, science labs">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .stats-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: var(--spacing-xl);
            margin-top: var(--spacing-2xl);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
        }
        
        .stats-banner-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: var(--spacing-xl);
            text-align: center;
        }
        
        .stat-item h3 {
            font-size: 3rem;
            margin-bottom: var(--spacing-sm);
            color: white;
            font-weight: 800;
        }
        
        .stat-item p {
            color: rgba(255, 255, 255, 0.95);
            margin: 0;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.875rem;
        }
        
        .how-it-works {
            margin-top: var(--spacing-2xl);
            padding: var(--spacing-2xl) 0;
        }
        
        .how-it-works-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
        }
        
        .how-it-works-header h2 {
            font-size: 2.5rem;
            margin-bottom: var(--spacing-md);
        }
        
        .how-it-works-header p {
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--spacing-2xl);
            margin-top: var(--spacing-xl);
        }
        
        .step-card {
            text-align: center;
            position: relative;
        }
        
        .step-number {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto var(--spacing-md);
            box-shadow: var(--shadow-md);
            transition: transform var(--transition);
        }
        
        .step-card:hover .step-number {
            transform: scale(1.1);
        }
        
        .step-card h3 {
            margin-bottom: var(--spacing-sm);
            color: var(--dark);
        }
        
        .step-card p {
            color: var(--text-light);
            margin: 0;
        }
        
        .cta-section {
            background: linear-gradient(135deg, var(--light), var(--primary-light));
            padding: var(--spacing-2xl);
            border-radius: var(--radius-xl);
            text-align: center;
            margin-top: var(--spacing-2xl);
            box-shadow: var(--shadow);
        }
        
        .cta-section h2 {
            font-size: 2rem;
            margin-bottom: var(--spacing-md);
        }
        
        .cta-section p {
            color: var(--text-light);
            margin-bottom: var(--spacing-xl);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cta-buttons {
            display: flex;
            gap: var(--spacing-md);
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .testimonial-box {
            background: var(--white);
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            margin-top: var(--spacing-2xl);
            border-left: 4px solid var(--primary-color);
        }
        
        .testimonial-box p {
            font-style: italic;
            color: var(--text);
            margin-bottom: var(--spacing-sm);
        }
        
        .testimonial-author {
            font-weight: 600;
            color: var(--dark);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="hero-section">
        <div class="container">
            <?php displayFlash(); ?>
            
            <!-- Hero Content -->
            <div class="hero-content">
                <h1>Laboratory Booking Made Simple</h1>
                <p class="hero-subtitle">
                    Reserve your lab space online with our easy-to-use booking system. 
                    View availability in real-time and manage all your bookings in one place.
                </p>
                
                <?php if (isLoggedIn()): ?>
                    <!-- Logged in users - show dashboard/booking buttons -->
                    <div class="hero-actions">
                        <?php if (isAdmin()): ?>
                            <a href="admin/dashboard.php" class="btn btn-primary btn-lg">
                                📊 Admin Dashboard
                            </a>
                            <a href="admin/manage_labs.php" class="btn btn-secondary btn-lg">
                                ⚙️ Manage Labs
                            </a>
                        <?php else: ?>
                            <a href="member/dashboard.php" class="btn btn-primary btn-lg">
                                📊 My Dashboard
                            </a>
                            <a href="member/book.php" class="btn btn-secondary btn-lg">
                                🔬 Book a Lab
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Guest users - show register/login buttons -->
                    <div class="hero-actions">
                        <a href="auth/register.php" class="btn btn-primary btn-lg">
                            ✨ Get Started Free
                        </a>
                        <a href="auth/login.php" class="btn btn-secondary btn-lg">
                            🔒 Login
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Stats Banner -->
            <div class="stats-banner">
                <div class="stats-banner-grid">
                    <div class="stat-item">
                        <h3><?php echo $totalLabs; ?></h3>
                        <p>Active Labs</p>
                    </div>
                    <div class="stat-item">
                        <h3><?php echo $totalUsers; ?>+</h3>
                        <p>Registered Users</p>
                    </div>
                    <div class="stat-item">
                        <h3><?php echo $totalBookings; ?>+</h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
            </div>
            
            <!-- Features Section -->
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔬</div>
                    <h3>Modern Facilities</h3>
                    <p>Access to state-of-the-art laboratory equipment and facilities for all your research needs</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📅</div>
                    <h3>Easy Booking</h3>
                    <p>Simple and intuitive booking process that takes less than 2 minutes to complete</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">⏰</div>
                    <h3>Real-time Availability</h3>
                    <p>See available time slots instantly and book your preferred lab sessions immediately</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Track History</h3>
                    <p>View all your past and upcoming bookings with detailed information and receipts</p>
                </div>
            </div>
            
            <!-- How It Works -->
            <div class="how-it-works">
                <div class="how-it-works-header">
                    <h2>How It Works</h2>
                    <p>Get started with lab bookings in three simple steps</p>
                </div>
                
                <div class="steps-grid">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h3>Create Account</h3>
                        <p>Sign up with your email and create a secure account in less than a minute. It's completely free!</p>
                    </div>
                    
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h3>Browse Labs</h3>
                        <p>View available laboratories, check their equipment, capacity, and see real-time availability</p>
                    </div>
                    
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h3>Book & Confirm</h3>
                        <p>Select your preferred time slot, confirm your booking, and receive instant confirmation</p>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial (Optional) -->
            <div class="testimonial-box">
                <p>"This lab booking system has made scheduling our research sessions so much easier. The interface is intuitive and the real-time availability is a game changer!"</p>
                <div class="testimonial-author">
                    - Dr. Sarah Johnson, Research Coordinator
                </div>
            </div>
            
            <!-- Call to Action Section -->
            <div class="cta-section">
                <h2>Ready to Get Started?</h2>
                <p>
                    Join hundreds of researchers and students who trust our platform for their laboratory bookings. 
                    Start booking today and experience the convenience of our modern system.
                </p>
                
                <?php if (!isLoggedIn()): ?>
                    <div class="cta-buttons">
                        <a href="auth/register.php" class="btn btn-primary btn-lg">
                            Create Free Account
                        </a>
                        <a href="auth/login.php" class="btn btn-secondary btn-lg">
                            Sign In
                        </a>
                    </div>
                <?php else: ?>
                    <div class="cta-buttons">
                        <?php if (isAdmin()): ?>
                            <a href="admin/dashboard.php" class="btn btn-primary btn-lg">
                                Go to Admin Dashboard
                            </a>
                            <a href="admin/reports.php" class="btn btn-secondary btn-lg">
                                View Reports
                            </a>
                        <?php else: ?>
                            <a href="member/book.php" class="btn btn-primary btn-lg">
                                Book Your First Lab
                            </a>
                            <a href="member/history.php" class="btn btn-secondary btn-lg">
                                View My Bookings
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>