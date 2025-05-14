<!-- Sound -->
<audio id="popup-sound" src="{{ asset('sounds/notific.mp3') }}" preload="auto"></audio>

<!-- Popup Container -->
<div id="wave-popup" class="hide">
    <div id="wave-message">Hi there!</div>
    <img src="{{ asset('images/waving-handd.gif') }}" alt="Wave">
</div>

<style>
    #wave-popup {
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: flex;
        flex-direction: row; /* Place bubble and GIF side by side */
        align-items: flex-start; /* Align to top */
        gap: 10px;
        background: transparent;
        padding: 10px;
        z-index: 9999;
        opacity: 0;
        transform: translateY(100px);
        transition: all 0.4s ease-in-out;
    }

    #wave-popup.show {
        opacity: 1;
        animation: bounceInUp 0.6s ease forwards;
    }

    #wave-popup.hide {
        opacity: 0;
        transform: translateY(100px);
    }

    #wave-popup img {
        width: 60px;
        height: 60px;
        background: #808080; /* Grey background for GIF */
        border-radius: 10px; /* Optional: rounded corners for background */
    }

    #wave-message {
        position: relative;
        background: #db781b; /* Green background for bubble */
        padding: 10px 15px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        font-size: 14px;
        color: #333;
        max-width: 220px;
        text-align: center;
        font-weight: 500;
        order: -1; /* Place bubble before GIF */
        transform: translateY(-10px); /* Slightly higher for "mind" effect */
    }

    /* Thought bubble tail (pointing right to GIF's head) */
    #wave-message::before {
        content: '';
        position: absolute;
        top: 20px; /* Adjusted to point to GIF's head */
        right: -10px; /* Position to right of bubble */
        width: 10px;
        height: 10px;
        background: #db781b; /* Green tail to match bubble */
        border-radius: 50%;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }

    #wave-message::after {
        content: '';
        position: absolute;
        top: 24px; /* Slightly below first circle */
        right: -16px; /* Further right */
        width: 6px;
        height: 6px;
        background: #db781b; /* Green tail to match bubble */
        border-radius: 50%;
        box-shadow: 0 0 4px rgba(0, 0, 0, 0.1);
    }

    @keyframes bounceInUp {
        0% {
            opacity: 0;
            transform: translateY(100px);
        }
        60% {
            opacity: 1;
            transform: translateY(-10px);
        }
        80% {
            transform: translateY(5px);
        }
        100% {
            transform: translateY(0);
        }
    }
</style>

<script>
    const messages = @json(__('messages.finance_motivation'));

    const popup = document.getElementById('wave-popup');
    const message = document.getElementById('wave-message');
    const sound = document.getElementById('popup-sound');

    function showPopup() {
        const randomMessage = messages[Math.floor(Math.random() * messages.length)];
        message.innerText = randomMessage;

        popup.classList.remove('hide');
        popup.classList.add('show');

        try {
            sound.currentTime = 0;
            sound.play();
        } catch (err) {
            console.warn("Sound failed to play:", err);
        }

        setTimeout(() => {
            popup.classList.remove('show');
            popup.classList.add('hide');
        }, 8000);
    }

    window.onload = () => {
        setTimeout(() => {
            showPopup();
        }, 1000);

        setInterval(showPopup, 20000);
    };
</script>
