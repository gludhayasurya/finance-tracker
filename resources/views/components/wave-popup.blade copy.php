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
        opacity: 0;
        transform: translateY(100px);
        transition: all 0.5s ease-in-out;
        z-index: 9999;
    }

    #wave-popup img {
        width: 40px;
        height: 40px;
    }

    #wave-popup.show {
        opacity: 1;
        transform: translateY(0);
    }

    #wave-popup.hide {
        opacity: 0;
        transform: translateY(100px);
    }
</style>

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

    function showPopup() {
        const randomMessage = messages[Math.floor(Math.random() * messages.length)];
        message.innerText = randomMessage;

        popup.classList.remove('hide');
        popup.classList.add('show');

        setTimeout(() => {
            popup.classList.remove('show');
            popup.classList.add('hide');
        }, 4000); // show for 4 seconds
    }

    window.onload = () => {
        setTimeout(showPopup, 1000); // initial delay
        setInterval(showPopup, 10000); // repeat every 10s
    };
</script>
