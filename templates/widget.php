<?php
/**
 * Rating Bulls - Full Widget Template (New Design)
 * 
 * Variables available:
 * - $company: Array with company data
 * - $reviews: Array of review objects
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get rating label and color
$rating_data = rbr_get_rating_data($company['averageRating']);
$formatted_rating = floor($company['averageRating'] * 10) / 10;
$formatted_rating = number_format($formatted_rating, 1);

// Build the Rating Bulls profile URL
$domain = get_option('rbr_company_domain', '');
$profile_url = 'https://ratingbulls.com/company/' . esc_attr($domain);
?>

<div class="rbr-widget">
    <!-- Left Section: Branding and Rating -->
    <div class="rbr-widget-header">
        <!-- Logo -->
        <div class="rbr-logo">
            <svg class="rbr-svg-logo" viewBox="0 0 172 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_8685_14503)">
                <path d="M29.1078 32.507L29.8501 28.4224C29.2075 29.8222 28.3236 30.9149 27.1985 31.7004C26.0733 32.4883 24.8322 32.8798 23.4727 32.8798C21.8905 32.8798 20.7051 32.3616 19.914 31.3252C19.1229 30.2888 18.877 28.9218 19.1716 27.2218L20.5798 19.0525C20.8767 17.3526 21.6052 15.9855 22.7675 14.9491C23.9297 13.9127 25.3008 13.3945 26.883 13.3945C28.2424 13.3945 29.3421 13.7885 30.1819 14.574C31.0217 15.3618 31.5158 16.4545 31.6643 17.852L32.3696 13.7674H38.2644L34.9655 32.5047H29.1078V32.507ZM31.1841 20.6259C31.307 19.8756 31.1841 19.2706 30.8129 18.8087C30.4417 18.3468 29.8733 18.1146 29.1078 18.1146C28.3422 18.1146 27.7228 18.3468 27.1799 18.8087C26.6371 19.2706 26.303 19.8779 26.1777 20.6259L25.324 25.6485C25.2011 26.3988 25.324 27.0038 25.6952 27.4657C26.0664 27.9276 26.6232 28.1598 27.3632 28.1598C28.1033 28.1598 28.7853 27.9276 29.3282 27.4657C29.871 27.0038 30.2051 26.3988 30.328 25.6485L31.1818 20.6259H31.1841Z" fill="white"/>
                <path d="M40.3429 18.4891H38.1553L39.009 13.7667H41.1595L41.8648 9.64453H48.7248L45.0547 13.7667H50.2466L49.43 18.4891H46.2053L44.76 26.7335C44.7113 27.0336 44.76 27.2821 44.9085 27.4838C45.057 27.6831 45.2913 27.7839 45.6138 27.7839H47.8014L46.9848 32.5064H43.6117C41.881 32.5064 40.5842 31.9764 39.7189 30.9142C38.8536 29.852 38.5821 28.4592 38.9023 26.7358L40.3476 18.4915L40.3429 18.4891Z" fill="white"/>
                <path d="M66.4094 32.507L68.4857 20.5157C68.6087 19.7912 68.5043 19.2096 68.1702 18.7735C67.8361 18.3374 67.3234 18.117 66.6321 18.117C65.9408 18.117 65.384 18.3421 64.8899 18.7923C64.3957 19.2425 64.0988 19.8169 63.999 20.5157L61.8856 32.507H56.0278L59.2896 13.7697H65.1845L64.4792 17.7418C65.1219 16.3677 65.9756 15.2985 67.0381 14.5365C68.1006 13.7744 69.2884 13.3945 70.5968 13.3945C72.1048 13.3945 73.2299 13.901 73.97 14.9116C74.7123 15.9246 74.9327 17.2658 74.6381 18.94L72.2648 32.507H66.4071H66.4094Z" fill="white"/>
                <path d="M74.0469 39.9995L74.9006 35.277H81.5007C82.3405 35.277 83.0643 35.0214 83.6698 34.5079C84.2753 33.9968 84.6535 33.3144 84.7996 32.4656L85.5049 28.4185C84.8623 29.8183 83.9784 30.911 82.8532 31.6965C81.7281 32.4844 80.4869 32.8759 79.1275 32.8759C77.5453 32.8759 76.3598 32.3577 75.5687 31.3213C74.7776 30.2849 74.5317 28.9179 74.8264 27.2179L76.2345 19.0486C76.5315 17.3487 77.2599 15.9816 78.4222 14.9452C79.5845 13.9088 80.9556 13.3906 82.5377 13.3906C83.8972 13.3906 84.9968 13.7846 85.8366 14.5701C86.6764 15.3579 87.1706 16.4506 87.3191 17.8481L88.0243 13.7634H93.9192L90.6574 32.4633C90.2375 34.7377 89.2306 36.5596 87.6346 37.9337C86.0408 39.3077 84.1176 39.9948 81.8696 39.9948H74.0469V39.9995ZM84.9667 27.4641C85.4979 27.0022 85.8389 26.3972 85.9874 25.6469L86.8411 20.5868C86.9641 19.8623 86.8342 19.269 86.4514 18.8071C86.0686 18.3452 85.5049 18.1131 84.7648 18.1131C84.0248 18.1131 83.3799 18.3452 82.837 18.8071C82.2941 19.269 81.9601 19.8764 81.8348 20.6243L80.9811 25.6469C80.8581 26.3972 80.9811 27.0022 81.3523 27.4641C81.7234 27.9261 82.2802 28.1582 83.0203 28.1582C83.7603 28.1582 84.4354 27.9261 84.9667 27.4641Z" fill="white"/>
                <path d="M48.2031 32.5053H54.0609L57.3621 13.7656H51.4672L48.2031 32.5053Z" fill="white"/>
                <path d="M51.7988 11.4458H57.8074L58.9186 5.22266L52.1677 9.38234L51.7988 11.4458Z" fill="white"/>
                <path d="M97.5936 6.27344H107.567C109.469 6.27344 110.891 6.85964 111.831 8.03438C112.77 9.20913 113.079 10.7567 112.759 12.6818L112.722 12.9819C112.476 14.5318 111.863 15.8543 110.887 16.954C109.91 18.0537 108.706 18.7783 107.272 19.1276C108.806 19.4278 109.929 20.1523 110.645 21.3013C111.362 22.4502 111.573 23.8876 111.276 25.611L111.202 25.9112C110.857 27.9089 109.98 29.5081 108.569 30.7086C107.161 31.9068 105.479 32.5071 103.526 32.5071H93.0327L97.5936 6.27344ZM101.152 21.7515L100.15 27.372H102.449C103.141 27.372 103.723 27.1656 104.191 26.7529C104.66 26.3403 104.957 25.7963 105.082 25.1233L105.305 24.0353C105.428 23.36 105.317 22.8113 104.971 22.3869C104.625 21.9625 104.117 21.7491 103.451 21.7491H101.152V21.7515ZM102.932 11.4086L102.041 16.6562H103.783C104.451 16.6562 105.031 16.4428 105.525 16.0184C106.02 15.594 106.328 15.043 106.453 14.37L106.528 13.6572C106.651 12.9819 106.553 12.4403 106.231 12.0276C105.908 11.6149 105.402 11.4086 104.711 11.4086H102.932Z" fill="#00FF55"/>
                <path d="M133.327 11.2635L129.667 32.5075H135.527L139.893 7.21875L133.327 11.2635Z" fill="#00FF55"/>
                <path d="M141.721 6.10338L137.515 32.5059H143.372L148.286 2.05859L141.721 6.10338Z" fill="#00FF55"/>
                <path d="M124.435 16.6956L123.029 25.8309C122.906 26.5297 122.598 27.0924 122.101 27.5168C121.607 27.9412 121.039 28.1546 120.396 28.1546C119.705 28.1546 119.192 27.9366 118.858 27.4981C118.524 27.0619 118.419 26.4804 118.542 25.7559L120.656 13.7646H114.761L112.425 27.3316C112.128 29.0058 112.344 30.3494 113.074 31.36C113.803 32.3729 114.921 32.8771 116.429 32.8771C117.74 32.8771 118.932 32.4902 120.006 31.714C121.08 30.9402 121.941 29.878 122.584 28.5274L121.878 32.4995H127.736L130.998 12.6484H130.996L124.433 16.6932L124.435 16.6956Z" fill="#00FF55"/>
                <path d="M155.313 13.3935C155.011 13.3935 154.717 13.4076 154.429 13.4263C154.369 13.3935 154.311 13.3513 154.262 13.2974C153.796 12.1883 155.552 10.3218 157.883 8.2584C161.166 5.24298 162.14 2.55115 162.087 0C162.089 0.135999 161.762 0.466616 161.679 0.576822C161.526 0.780819 161.368 0.982472 161.206 1.17944C160.862 1.59447 160.5 1.99308 160.124 2.37763C159.299 3.21941 158.405 3.9932 157.482 4.72243C156.301 5.65566 155.067 6.52793 153.798 7.33454C152.629 8.07785 151.367 8.7766 150.529 9.92321C150.174 10.4086 149.905 10.9784 149.882 11.5833C149.861 12.1039 150.028 12.6244 150.302 13.0652C150.587 13.5225 150.972 13.8578 151.406 14.0946C150.773 14.3502 150.186 14.6691 149.65 15.0654C148.142 16.1768 147.216 17.7314 146.868 19.7315C146.597 21.3307 146.88 22.5617 147.722 23.4222C148.562 24.2851 150.107 24.9159 152.357 25.3145L153.545 25.5396C154.459 25.6897 155.09 25.8632 155.436 26.0648C155.782 26.2641 155.93 26.515 155.881 26.8152C155.707 27.589 154.879 27.9758 153.397 27.9758C152.483 27.9758 151.796 27.8141 151.339 27.4881C150.882 27.1622 150.703 26.7143 150.801 26.1399H145.572C145.374 28.2643 145.899 29.9197 147.147 31.1062C148.395 32.2926 150.256 32.8859 152.726 32.8859C155.197 32.8859 157.146 32.3301 158.714 31.2187C160.285 30.1073 161.24 28.5527 161.588 26.5525C161.86 24.9792 161.588 23.7786 160.772 22.9556C159.955 22.1303 158.447 21.5183 156.248 21.1196L155.062 20.8945C154.049 20.6952 153.369 20.4936 153.023 20.2943C152.678 20.095 152.529 19.8183 152.578 19.4689C152.726 18.6951 153.469 18.3059 154.803 18.3059C156.385 18.3059 157.102 18.9179 156.953 20.1419H160.022C160.034 20.1419 160.046 20.1419 160.057 20.1419C160.155 20.1536 160.252 20.1583 160.349 20.1559C160.946 20.1419 161.514 19.8792 162.001 19.5275C163.147 18.6998 163.859 17.4336 164.611 16.2636C165.428 14.9927 166.309 13.7593 167.251 12.5799C167.987 11.656 168.766 10.7673 169.613 9.94431C169.998 9.56914 170.397 9.20804 170.815 8.87039C171.012 8.7086 171.211 8.55384 171.418 8.40143C171.529 8.31936 171.861 7.99343 171.995 7.99812C169.474 7.90433 166.794 8.84694 163.762 12.1179C162.632 13.3818 161.563 14.4721 160.646 15.1287C160.185 14.6808 159.635 14.3197 158.999 14.0454C158.999 14.0454 157.389 13.4357 155.311 13.3959L155.313 13.3935Z" fill="#00FF55"/>
                <path d="M18.7611 8.46583C17.5756 7.00502 15.8078 6.27344 13.4578 6.27344H4.56093L0 32.5071H6.22895L8.26814 20.9261L12.0867 32.5071H16.9075L17.5756 28.5725L15.0539 21.6014C17.6754 20.2274 19.2691 17.9412 19.8375 14.7429L19.9117 14.2176C20.3316 11.8447 19.9489 9.92664 18.7634 8.46583H18.7611ZM9.69256 8.63934L10.9453 12.5388H8.43749L9.69024 8.63934H9.69256ZM10.8618 17.2987L12.0774 16.063L12.9729 18.8486L10.8618 17.2964V17.2987ZM6.81124 18.7923L7.98975 15.1251L6.17791 13.7932L15.6779 12.5411L6.81124 18.7923Z" fill="white"/>
                </g>
                <defs>
                <clipPath id="clip0_8685_14503">
                <rect width="172" height="40" fill="white"/>
                </clipPath>
                </defs>
            </svg>
        </div>
        
        <!-- Rating Score -->
        <div class="rbr-rating-box" style="background-color: <?php echo esc_attr($rating_data['color']); ?>;">
            <span class="rbr-rating-number"><?php echo esc_html($formatted_rating); ?></span>
            <span class="rbr-rating-label"><?php echo esc_html($rating_data['label']); ?></span>
        </div>
        
        <!-- Bull Icons -->
        <div class="rbr-bull-rating">
            <?php echo rbr_render_bull_icons($company['averageRating']); ?>
        </div>
        
        <!-- Review Count -->
        <div class="rbr-review-count">
            Based on <?php echo esc_html(number_format($company['totalReviews'])); ?> reviews
        </div>
    </div>
    
    <!-- Right Section: Reviews -->
    <div class="rbr-widget-body">
        <div class="rbr-reviews-header">
            <h4 class="rbr-reviews-title">Recent reviews</h4>
            <a href="<?php echo esc_url($profile_url); ?>" target="_blank" rel="noopener noreferrer" class="rbr-see-all-link">
                See all reviews
                <span class="rbr-arrow">
                    <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.8335 5.83203H14.1668V14.1654" stroke="#003F88" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5.8335 14.1654L14.1668 5.83203" stroke="#003F88" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </a>
        </div>
        
        <?php if (!empty($reviews)): ?>
            <div class="rbr-reviews-grid">
                <?php foreach ($reviews as $index => $review): ?>
                    <div class="rbr-review-card" data-index="<?php echo esc_attr($index); ?>">
                        <!-- Review Header: Avatar and Name -->
                        <div class="rbr-review-card-header">
                            <div class="rbr-reviewer-avatar" style="background-color: <?php echo esc_attr(rbr_get_avatar_color($review['customerName'])); ?>;">
                                <?php echo esc_html(rbr_get_initials($review['customerName'])); ?>
                            </div>
                            <div class="rbr-reviewer-info">
                                <span class="rbr-reviewer-name"><?php echo esc_html($review['customerName']); ?></span>
                                <div class="rbr-review-bulls">
                                    <?php echo rbr_render_bull_icons($review['rating'], 'sm'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Review Content -->
                        <h5 class="rbr-review-title"><?php echo esc_html($review['title']); ?></h5>
                        <p class="rbr-review-comment"><?php echo esc_html($review['comment']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Mobile Carousel Dots -->
            <div class="rbr-carousel-dots">
                <?php foreach ($reviews as $index => $review): ?>
                    <span class="rbr-dot <?php echo $index === 0 ? 'rbr-dot-active' : ''; ?>" data-index="<?php echo esc_attr($index); ?>"></span>
                <?php endforeach; ?>
            </div>
            
            <!-- Carousel JavaScript -->
            <!-- Carousel JavaScript -->
            <script>
            (function() {
                function initCarousel() {
                    const container = document.querySelector('.rbr-reviews-grid');
                    const dots = document.querySelectorAll('.rbr-carousel-dots .rbr-dot');
                    const cards = document.querySelectorAll('.rbr-reviews-grid .rbr-review-card');
                    
                    if (dots.length === 0 || cards.length === 0) return;
                    
                    let currentIndex = 0;
                    let autoPlayInterval = null;
                    let touchStartX = 0;
                    let touchEndX = 0;
                    const autoPlayDelay = 3000; // 3 seconds
                    
                    // Function to show a specific slide
                    function showSlide(index) {
                        // Handle wraparound
                        if (index >= cards.length) index = 0;
                        if (index < 0) index = cards.length - 1;
                        
                        currentIndex = index;
                        
                        // Update dots
                        dots.forEach(function(d, i) {
                            if (i === index) {
                                d.classList.add('rbr-dot-active');
                            } else {
                                d.classList.remove('rbr-dot-active');
                            }
                        });
                        
                        // Update cards
                        cards.forEach(function(card, i) {
                            card.style.display = (i === index) ? 'flex' : 'none';
                        });
                    }
                    
                    // Dot click handlers
                    dots.forEach(function(dot) {
                        dot.addEventListener('click', function() {
                            const index = parseInt(this.getAttribute('data-index'));
                            showSlide(index);
                            resetAutoPlay(); // Reset timer on manual interaction
                        });
                    });
                    
                    // Touch/Swipe handlers
                    container.addEventListener('touchstart', function(e) {
                        touchStartX = e.changedTouches[0].screenX;
                    }, { passive: true });
                    
                    container.addEventListener('touchend', function(e) {
                        touchEndX = e.changedTouches[0].screenX;
                        handleSwipe();
                    }, { passive: true });
                    
                    function handleSwipe() {
                        const swipeThreshold = 50; // Minimum swipe distance
                        const diff = touchStartX - touchEndX;
                        
                        if (Math.abs(diff) > swipeThreshold) {
                            if (diff > 0) {
                                // Swiped left - next slide
                                showSlide(currentIndex + 1);
                            } else {
                                // Swiped right - previous slide
                                showSlide(currentIndex - 1);
                            }
                            resetAutoPlay();
                        }
                    }
                    
                    // Auto-play functionality
                    function startAutoPlay() {
                        autoPlayInterval = setInterval(function() {
                            showSlide(currentIndex + 1);
                        }, autoPlayDelay);
                    }
                    
                    function resetAutoPlay() {
                        clearInterval(autoPlayInterval);
                        startAutoPlay();
                    }
                    
                    // Pause auto-play when user hovers (desktop) or touches
                    container.addEventListener('mouseenter', function() {
                        clearInterval(autoPlayInterval);
                    });
                    
                    container.addEventListener('mouseleave', function() {
                        startAutoPlay();
                    });
                    
                    // Start auto-play only on mobile
                    function checkMobileAndStart() {
                        if (window.innerWidth <= 640) {
                            startAutoPlay();
                        } else {
                            clearInterval(autoPlayInterval);
                        }
                    }
                    
                    // Check on load and resize
                    checkMobileAndStart();
                    window.addEventListener('resize', checkMobileAndStart);
                }
                
                // Initialize
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initCarousel);
                } else {
                    initCarousel();
                }
            })();
            </script>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="rbr-widget-footer">
            <span class="rbr-footer-text">
                Verified reviews powered by <a href="https://ratingbulls.com" target="_blank" rel="noopener noreferrer">Rating Bulls</a> - The trusted platform for brokerage reviews
            </span>
        </div>
    </div>
</div>
