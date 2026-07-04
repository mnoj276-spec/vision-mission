@extends('layouts.app')

@section('title', 'You are Offline — GovJobs Standby')

@section('content')
<style>
    .offline-container {
        max-width: 700px;
        margin: 5rem auto;
        padding: 0 5%;
        text-align: center;
        font-family: 'Outfit', sans-serif;
    }
    
    .offline-card {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        padding: 3rem 2rem;
        box-shadow: var(--card-shadow);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        position: relative;
        overflow: hidden;
    }

    .offline-icon-wrapper {
        width: 100px;
        height: 100px;
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem auto;
        font-size: 3rem;
        animation: pulse-red 2s infinite ease-in-out;
    }

    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        70% { box-shadow: 0 0 0 20px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    .offline-title {
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, var(--text-primary) 30%, #ef4444 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .offline-lead {
        font-size: 1.1rem;
        color: var(--text-secondary);
        line-height: 1.6;
        margin-bottom: 2.5rem;
    }

    .diagnostic-panel {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.5rem;
        text-align: left;
        margin-bottom: 2.5rem;
    }

    .diagnostic-title {
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1rem;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .diagnostic-item {
        display: flex;
        justify-content: space-between;
        font-size: 0.88rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-secondary);
    }
    
    .diagnostic-item:last-child {
        border-bottom: none;
    }

    .diagnostic-val {
        font-weight: 700;
        color: var(--text-primary);
    }

    .retry-btn {
        background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-hover) 100%);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 1rem 2rem;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .retry-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        background: #ef4444;
    }
</style>

<div class="offline-container">
    <div class="offline-card">
        <div class="offline-icon-wrapper">
            📡
        </div>
        
        <h1 class="offline-title">Connection Interrupted</h1>
        <p class="offline-lead">
            Your device is currently disconnected from the internet. Do not panic! GovJobs standby caching engine is fully active. You can still browse offline pages or safely register alerts, which we will queue and automatically sync once you return online.
        </p>

        <div class="diagnostic-panel">
            <div class="diagnostic-title">
                <span class="status-dot"></span>
                PWA Core Standby Diagnostics
            </div>
            
            <div class="diagnostic-item">
                <span>Signal Status</span>
                <span class="diagnostic-val" id="diagSignal" style="color: #ef4444;">Offline</span>
            </div>
            <div class="diagnostic-item">
                <span>Standby Mode</span>
                <span class="diagnostic-val" style="color: #10b981;">Active (Shell cached)</span>
            </div>
            <div class="diagnostic-item">
                <span>Offline Storage Queue</span>
                <span class="diagnostic-val" id="diagQueue">0 pending items</span>
            </div>
        </div>

        <button class="retry-btn" id="pwaRetryBtn">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5"></path></svg>
            Simulate Connection Retry
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const retryBtn = document.getElementById('pwaRetryBtn');
        const diagSignal = document.getElementById('diagSignal');
        const statusDot = document.querySelector('.status-dot');

        // Check navigator signal properties dynamically
        function updateConnectionState() {
            if (navigator.onLine) {
                diagSignal.innerText = 'Connected';
                diagSignal.style.color = '#10b981';
                statusDot.style.background = '#10b981';
                retryBtn.innerHTML = 'Back Online! Reloading...';
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                diagSignal.innerText = 'Offline';
                diagSignal.style.color = '#ef4444';
                statusDot.style.background = '#ef4444';
            }
        }

        window.addEventListener('online', updateConnectionState);
        window.addEventListener('offline', updateConnectionState);

        retryBtn.addEventListener('click', function() {
            retryBtn.classList.add('loading');
            showToast('Scanning signal network...', 'warning');
            
            setTimeout(() => {
                if (navigator.onLine) {
                    showToast('Connection verified successfully! Restoring session...', 'success');
                    window.location.reload();
                } else {
                    showToast('Still offline. Please check your data signal or Wi-Fi.', 'error');
                }
            }, 800);
        });

        // Query IndexedDB Pending queue count dynamically
        try {
            const dbReq = indexedDB.open('govjobs_offline_db', 1);
            dbReq.onsuccess = function(e) {
                const db = e.target.result;
                if (db.objectStoreNames.contains('subscriptions')) {
                    const tx = db.transaction(['subscriptions'], 'readonly');
                    const store = tx.objectStore('subscriptions');
                    const countReq = store.count();
                    countReq.onsuccess = function() {
                        document.getElementById('diagQueue').innerText = countReq.result + ' pending items';
                    };
                }
            };
        } catch(e) {}
    });
</script>
@endsection
