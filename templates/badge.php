<?php
/**
 * Rating Bulls - Compact Badge Template
 * 
 * Variables available:
 * - $company: Array with company data
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$formatted_rating = number_format($company['averageRating'], 1);
?>

<div class="rbr-badge">
    <a href="https://ratingbulls.com/company/<?php echo esc_attr(parse_url($company['websiteUrl'], PHP_URL_HOST)); ?>" 
       target="_blank" 
       rel="noopener noreferrer"
       class="rbr-badge-link">
        <div class="rbr-badge-inner">
            <div class="rbr-badge-logo">
                <svg class="rbr-badge-bull-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 4C10.5 4 9 5 8 6.5C7 5 5.5 4 4 4C2.5 4 1 5.5 1 7.5C1 9 2 10.5 3.5 11.5L12 20L20.5 11.5C22 10.5 23 9 23 7.5C23 5.5 21.5 4 20 4C18.5 4 17 5 16 6.5C15 5 13.5 4 12 4Z" fill="#013F88"/>
                    <circle cx="8" cy="10" r="1.5" fill="#00FF55"/>
                    <circle cx="16" cy="10" r="1.5" fill="#00FF55"/>
                </svg>
            </div>
            <div class="rbr-badge-content">
                <div class="rbr-badge-rating">
                    <span class="rbr-badge-stars">
                        <?php 
                        $full_stars = floor($company['averageRating']);
                        for ($i = 0; $i < 5; $i++) {
                            if ($i < $full_stars) {
                                echo '<span class="rbr-star rbr-star-full">★</span>';
                            } else {
                                echo '<span class="rbr-star rbr-star-empty">★</span>';
                            }
                        }
                        ?>
                    </span>
                    <span class="rbr-badge-score"><?php echo esc_html($formatted_rating); ?></span>
                </div>
                <div class="rbr-badge-meta">
                    <?php echo esc_html(number_format($company['totalReviews'])); ?> reviews on <strong>Rating Bulls</strong>
                </div>
            </div>
            <?php if ($company['isVerified']): ?>
                <span class="rbr-badge-verified">✓</span>
            <?php endif; ?>
        </div>
    </a>
</div>
