<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ZedMemes - Meme Sharing Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bulma CSS -->
    <link rel="stylesheet" href="css/bulma.min.css">
    <style>
        /* Extra hover effect for meme cards */
        .meme-card.is-hoverable {
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .meme-card.is-hoverable:hover {
            box-shadow: 0 8px 32px rgba(0,0,0,0.3), 0 1.5px 6px rgba(0,0,0,0.12);
            transform: translateY(-6px) scale(1.03);
            cursor: pointer;
            z-index: 2;
        }
        .meme-card.is-hoverable:active {
            transform: translateY(-2px) scale(0.98);
        }
        /* Make sure stacked navbars don't overlap content on mobile */
        @media (max-width: 1023px) {
            .section {
                margin-top: 7rem !important; /* Adjust depending on navbar heights */
            }
        }
        @media (min-width: 1024px) {
            .section {
                margin-top: 6.5rem !important;
            }
        }
    </style>
</head>
<body class="has-background-black-ter has-text-light">
    <!-- Main Navbar -->
    <nav class="navbar is-primary is-fixed-top" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="#">
                <strong>ZedMemes</strong>
            </a>
            <!-- Burger for mobile -->
            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="mainNavbarMenu">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>
        <div id="mainNavbarMenu" class="navbar-menu">
            <div class="navbar-end" id="nav-auth">
                <!-- Auth buttons or user info injected here -->
            </div>
        </div>
    </nav>

    <!-- Fixed Upload Meme Button Navbar (stacked below main navbar) -->
    <nav class="navbar is-light is-fixed-top" style="top:3.25rem;" role="navigation" aria-label="upload navigation">
        <div class="navbar-brand">
            <!-- Burger for mobile upload bar -->
            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="uploadNavbarMenu">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>
        <div id="uploadNavbarMenu" class="navbar-menu" style="width:100%;">
            <div class="navbar-end" style="width:100%;">
                <div id="upload-btn-area" class="navbar-item" style="width:100%; text-align:right;">
                    <!-- Upload Meme Button injected by JS if user is logged in -->
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container (with margin for navbars) -->
    <section class="section">
        <div class="container">
            <!-- Meme Feed Grid -->
            <div class="columns is-multiline" id="meme-feed">
                <!-- Meme cards injected by JS -->
            </div>
        </div>
    </section>

    <!-- Meme Modal (for viewing images clearly) -->
    <div class="modal" id="meme-modal">
        <div class="modal-background"></div>
        <div class="modal-content">
            <p class="image is-4by3">
                <img id="modal-img" src="" alt="Meme preview">
            </p>
        </div>
        <button class="modal-close is-large" aria-label="close"></button>
    </div>

    <!-- Modals (Login, Signup, Upload) -->
    <div id="auth-modal"></div>
    <div id="upload-modal"></div>

    <!-- jQuery (required for AJAX and modals) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Main JS -->
    <script>
        // Bulma burger menu toggle for all burgers on the page
        $(document).on('click', '.navbar-burger', function() {
            var target = $(this).data('target');
            $(this).toggleClass('is-active');
            $('#' + target).toggleClass('is-active');
        });

        // Example for injecting an upload button if logged in (simulate)
        $(function() {
            // Simulate user is logged in
            $('#nav-auth').html(`
                <div class="navbar-item">
                    Welcome, <strong>jacksonm</strong>
                    <button class="button is-light ml-2" id="logout-btn">Logout</button>
                </div>
            `);
            $('#upload-btn-area').html('<button class="button is-link is-rounded is-medium">Upload Meme</button>');
        });

        // Open modal with clicked meme
        $(document).on('click', '.meme-card', function() {
            var imgSrc = $(this).data('img');
            $('#modal-img').attr('src', imgSrc);
            $('#meme-modal').addClass('is-active');
        });
        // Close modal
        $(document).on('click', '.modal-background, .modal-close', function() {
            $('#meme-modal').removeClass('is-active');
            $('#modal-img').attr('src', ''); // Clear image for reload
        });

        // Example rendering meme cards (replace this with your real JS rendering logic)
        // This is here just to show how your cards should look & behave
        $(function() {
            // Example meme images
            const memes = [
                { url: 'uploads/meme1.jpg', uploader: 'jacksonm' },
                { url: 'uploads/meme2.jpg', uploader: 'alice' },
                { url: 'uploads/meme3.jpg', uploader: 'bob' }
            ];
            let html = '';
            memes.forEach(meme => {
                html += `
                <div class="column is-one-quarter-desktop is-half-tablet is-full-mobile">
                  <div class="card is-hoverable meme-card" data-img="${meme.url}" style="cursor:pointer;">
                    <div class="card-image">
                      <figure class="image is-4by3">
                        <img src="${meme.url}" alt="Meme by ${meme.uploader}">
                      </figure>
                    </div>
                    <div class="card-content p-2">
                        <p class="has-text-grey-light is-size-7">Uploaded by: <strong>${meme.uploader}</strong></p>
                    </div>
                  </div>
                </div>
                `;
            });
            $('#meme-feed').html(html);
        });
    </script>
    <script src="js/main.js"></script>
</body>
</html>