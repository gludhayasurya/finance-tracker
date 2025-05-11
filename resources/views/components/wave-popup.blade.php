<style>
    #wave-popup {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #fff;
        padding: 10px 15px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 9999;
        opacity: 0;
        transform: translateY(100px);
        animation: none;
        transition: all 0.4s ease-in-out;
    }

    #wave-popup img {
        width: 40px;
        height: 40px;
    }

    #wave-popup.show {
        opacity: 1;
        animation: bounceInUp 0.6s ease forwards;
    }

    #wave-popup.hide {
        opacity: 0;
        transform: translateY(100px);
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

<!-- Sound -->
<audio id="popup-sound" src="{{ asset('sounds/notific.mp3') }}" preload="auto"></audio>

<!-- Popup -->
<div id="wave-popup" class="hide">
    <img src="{{ asset('images/waving-handd.gif') }}" alt="Wave">
    <span id="wave-message">Hi there!</span>
</div>

<script>
    const messages = [
        "Track your expenses today!",
        "💸 Don't forget to log your spending!",
        "Hi! Budgeting = Freedom 💰",
        "👋 Stay in control of your money!",
        "📊 Review your finance report weekly!"
    ];

    const popup = document.getElementById('wave-popup');
    const message = document.getElementById('wave-message');
    const sound = document.getElementById('popup-sound');

    // Function to show the popup and play sound
    function showPopup() {
        const randomMessage = messages[Math.floor(Math.random() * messages.length)];
        message.innerText = randomMessage;

        popup.classList.remove('hide');
        popup.classList.add('show');

        // Play sound (ensure user interaction triggers it)
        try {
            sound.currentTime = 0;
            sound.play();
        } catch (err) {
            console.warn("Sound failed to play:", err);
        }

        setTimeout(() => {
            popup.classList.remove('show');
            popup.classList.add('hide');
        }, 4000); // show for 4 seconds
    }

    window.onload = () => {
        // Show popup and play sound after 1 second
        setTimeout(() => {
            showPopup();
        }, 1000);

        // Repeat every 10 seconds
        //setInterval(showPopup, 60000);
    };
</script>
