@props(['url', 'title'])

<div class="social-share-container">
    <span class="share-label"><i class="fa-solid fa-share-nodes"></i> Share with Friends:</span>
    <div class="share-buttons">
        <a href="https://api.whatsapp.com/send?text={{ urlencode($title . ' - ' . $url) }}" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="share-btn whatsapp" 
           title="Share on WhatsApp">
            <i class="fa-brands fa-whatsapp"></i> WhatsApp
        </a>
        <a href="https://t.me/share/url?url={{ urlencode($url) }}&text={{ urlencode($title) }}" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="share-btn telegram" 
           title="Share on Telegram">
            <i class="fa-brands fa-telegram"></i> Telegram
        </a>
        <a href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}&text={{ urlencode($title) }}" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="share-btn twitter" 
           title="Share on Twitter/X">
            <i class="fa-brands fa-x-twitter"></i> Twitter
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="share-btn facebook" 
           title="Share on Facebook">
            <i class="fa-brands fa-facebook-f"></i> Facebook
        </a>
    </div>
</div>

<style>
    .social-share-container {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin: 1.5rem 0;
        padding: 1rem 1.25rem;
        background: rgba(255, 255, 255, 0.005);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        backdrop-filter: blur(8px);
    }
    .share-label {
        font-family: 'Outfit', sans-serif;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .share-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .share-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none !important;
        color: #fff !important;
        transition: all 0.2s ease;
    }
    .share-btn:hover {
        transform: translateY(-1px);
        opacity: 0.95;
    }
    .share-btn.whatsapp {
        background: #25D366;
        box-shadow: 0 4px 10px rgba(37, 211, 102, 0.15);
    }
    .share-btn.telegram {
        background: #229ED9;
        box-shadow: 0 4px 10px rgba(34, 158, 217, 0.15);
    }
    .share-btn.twitter {
        background: #14171A;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 10px rgba(20, 23, 26, 0.15);
    }
    .share-btn.facebook {
        background: #1877F2;
        box-shadow: 0 4px 10px rgba(24, 119, 242, 0.15);
    }
</style>
