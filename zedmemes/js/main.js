$(document).ready(function() {

    // ========== Utility ==========

    // Show modal (injects HTML and activates modal)
    function showModal(content, modalId='#auth-modal') {
        $(modalId).html(content).addClass('is-active');
    }

    // Close modals on background or close click
    $(document).on('click', '.modal-background, .modal-close, .modal-card-head .delete', function() {
        $('.modal').removeClass('is-active');
        // Special: clear meme modal image when closing it
        $('#modal-img').attr('src', '');
    });

    // ========== Auth Section ==========

    function renderAuthButtons() {
        $.post('php/auth.php', { action: 'status' }, function(data) {
            if (data.logged_in) {
                $('#nav-auth').html(`
                    <div class="navbar-item">
                        Welcome, <strong>${data.username}</strong>
                        <button class="button is-light ml-2" id="logout-btn">Logout</button>
                    </div>
                `);
                $('#upload-btn-area').html(`
                    <button class="button is-info" id="open-upload-modal">Upload Meme</button>
                `);
            } else {
                $('#nav-auth').html(`
                    <div class="navbar-item">
                        <button class="button is-light" id="open-login-modal">Login</button>
                        <button class="button is-primary ml-2" id="open-signup-modal">Sign Up</button>
                    </div>
                `);
                $('#upload-btn-area').html('');
            }
        }, 'json');
    }

    renderAuthButtons();

    // Show Login Modal
    $(document).on('click', '#open-login-modal', function() {
        showModal(`
        <div class="modal is-active">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Login</p>
                    <button class="delete" aria-label="close"></button>
                </header>
                <section class="modal-card-body">
                    <form id="login-form">
                        <div class="field">
                            <label class="label">Username</label>
                            <div class="control">
                                <input class="input" type="text" name="username" required>
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Password</label>
                            <div class="control">
                                <input class="input" type="password" name="password" required>
                            </div>
                        </div>
                        <button class="button is-primary" type="submit">Login</button>
                    </form>
                    <div id="login-msg" class="has-text-danger mt-2"></div>
                </section>
            </div>
        </div>
        `);
    });

    // Show Signup Modal
    $(document).on('click', '#open-signup-modal', function() {
        showModal(`
        <div class="modal is-active">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Sign Up</p>
                    <button class="delete" aria-label="close"></button>
                </header>
                <section class="modal-card-body">
                    <form id="signup-form">
                        <div class="field">
                            <label class="label">Username</label>
                            <div class="control">
                                <input class="input" type="text" name="username" required>
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Email</label>
                            <div class="control">
                                <input class="input" type="email" name="email" required>
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Password</label>
                            <div class="control">
                                <input class="input" type="password" name="password" required>
                            </div>
                        </div>
                        <button class="button is-primary" type="submit">Sign Up</button>
                    </form>
                    <div id="signup-msg" class="has-text-danger mt-2"></div>
                </section>
            </div>
        </div>
        `);
    });

    // Handle Login
    $(document).on('submit', '#login-form', function(e) {
        e.preventDefault();
        $.post('php/auth.php', $(this).serialize() + '&action=login', function(data) {
            if (data.success) {
                $('.modal').removeClass('is-active');
                renderAuthButtons();
                fetchMemes();
            } else {
                $('#login-msg').text(data.message);
            }
        }, 'json');
    });

    // Handle Signup
    $(document).on('submit', '#signup-form', function(e) {
        e.preventDefault();
        $.post('php/auth.php', $(this).serialize() + '&action=signup', function(data) {
            if (data.success) {
                $('.modal').removeClass('is-active');
                renderAuthButtons();
                fetchMemes();
            } else {
                $('#signup-msg').text(data.message);
            }
        }, 'json');
    });

    // Logout
    $(document).on('click', '#logout-btn', function() {
        $.post('php/auth.php', { action: 'logout' }, function() {
            renderAuthButtons();
            fetchMemes();
        });
    });

    // ========== Meme Upload Modal ==========

    $(document).on('click', '#open-upload-modal', function() {
        showModal(`
        <div class="modal is-active">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Upload Meme</p>
                    <button class="delete" aria-label="close"></button>
                </header>
                <section class="modal-card-body">
                    <form id="upload-form" enctype="multipart/form-data">
                        <div class="field">
                            <label class="label">Meme Image</label>
                            <div class="control">
                                <input class="input" type="file" name="meme_image" accept="image/*" required>
                            </div>
                        </div>
                        <button class="button is-success" type="submit">Upload</button>
                    </form>
                    <div id="upload-msg" class="has-text-danger mt-2"></div>
                </section>
            </div>
        </div>
        `, '#upload-modal');
    });

    // Handle Meme Upload
    $(document).on('submit', '#upload-form', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'upload');
        $.ajax({
            url: 'php/upload.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    $('.modal').removeClass('is-active');
                    fetchMemes();
                } else {
                    $('#upload-msg').text(data.message);
                }
            },
            error: function(xhr, status, error) {
                $('#upload-msg').text('Upload failed. Please try again.');
            }
        });
    });

    // ========== Meme Modal View (Google Images Style) ==========

    // Hold meme list and current index for navigation
    let memeList = [];
    let currentMemeIdx = -1;

    // Fetch memes from backend (for modal navigation, set memeList)
    function fetchMemes() {
        $.get('php/fetch_memes.php', function(data) {
            let grid = '';
            memeList = Array.isArray(data) ? data : [];
            if (memeList.length === 0) {
                grid = '<div class="column is-12 has-text-centered">No memes yet. Be the first to upload!</div>';
            } else {
                memeList.forEach(function(meme, idx) {
                    grid += `
                    <div class="column is-one-quarter-desktop is-half-tablet is-full-mobile">
                        <div class="card meme-card is-hoverable" data-img="${meme.image_path}" data-idx="${idx}" style="cursor:pointer;">
                            <div class="card-image">
                                <figure class="image is-4by3">
                                    <img src="${meme.image_path}" alt="Meme image">
                                </figure>
                            </div>
                            <div class="card-content">
                                <div class="buttons mb-2">
                                    <button class="button is-small is-success react-btn" data-id="${meme.id}" data-type="like">
                                        👍 Like <span class="tag is-light ml-1">${meme.likes}</span>
                                    </button>
                                    <button class="button is-small is-info react-btn" data-id="${meme.id}" data-type="upvote">
                                        ⬆️ Upvote <span class="tag is-light ml-1">${meme.upvotes}</span>
                                    </button>
                                    <button class="button is-small is-link share-btn" data-img="${meme.image_path}">
                                        🔗 Share
                                    </button>
                                    <a class="button is-small is-warning" href="${meme.image_path}" download>
                                        ⬇️ Download
                                    </a>
                                </div>
                                <p class="is-size-7 has-text-grey">Uploaded by ${meme.username} on ${meme.uploaded_at}</p>
                            </div>
                        </div>
                    </div>
                    `;
                });
            }
            $('#meme-feed').html(grid);
        }, 'json')
        .fail(function() {
            $('#meme-feed').html('<div class="column is-12 has-text-centered has-text-danger">Failed to load memes. Please try again later.</div>');
            memeList = [];
        });
    }

    fetchMemes();

    // ========== Meme Modal Open/Navigation ==========

    // Ensure modal HTML exists
    if (!$('#meme-modal').length) {
        $('body').append(`
            <div class="modal" id="meme-modal">
                <div class="modal-background"></div>
                <div class="modal-content">
                    <div style="position:relative;">
                        <button class="button is-medium is-white modal-nav prev-meme" style="position:absolute;left:-60px;top:45%;z-index:2;">&#8592;</button>
                        <img id="modal-img" src="" alt="Meme preview" style="max-width:100%;max-height:75vh;border-radius:8px;box-shadow:0 2px 10px #0008;">
                        <button class="button is-medium is-white modal-nav next-meme" style="position:absolute;right:-60px;top:45%;z-index:2;">&#8594;</button>
                    </div>
                </div>
                <button class="modal-close is-large" aria-label="close"></button>
            </div>
        `);
    }

    // Open meme modal
    $(document).on('click', '.meme-card', function(e) {
        // Prevent click if inside a button
        if ($(e.target).is('button, .button, a')) return;
        let idx = Number($(this).attr('data-idx'));
        showMemeModal(idx);
    });

    function showMemeModal(idx) {
        if (memeList.length === 0) return;
        currentMemeIdx = idx;
        let meme = memeList[idx];
        $('#modal-img').attr('src', meme.image_path);
        $('#meme-modal').addClass('is-active');
        // Show/hide nav buttons
        $('.prev-meme').toggle(idx > 0);
        $('.next-meme').toggle(idx < memeList.length - 1);
    }

    

    // Prev/Next navigation
    $(document).on('click', '.modal-nav', function(e) {
        e.stopPropagation();
        if (!memeList.length) return;
        if ($(this).hasClass('prev-meme') && currentMemeIdx > 0) {
            showMemeModal(currentMemeIdx - 1);
        } else if ($(this).hasClass('next-meme') && currentMemeIdx < memeList.length - 1) {
            showMemeModal(currentMemeIdx + 1);
        }
    });

    // Keyboard navigation for modal (left/right/esc)
    $(document).on('keydown', function(e) {
        if (!$('#meme-modal').hasClass('is-active')) return;
        if (!memeList.length) return;
        if (e.key === 'ArrowLeft' && currentMemeIdx > 0) {
            showMemeModal(currentMemeIdx - 1);
        } else if (e.key === 'ArrowRight' && currentMemeIdx < memeList.length - 1) {
            showMemeModal(currentMemeIdx + 1);
        } else if (e.key === 'Escape') {
            $('#meme-modal').removeClass('is-active');
            $('#modal-img').attr('src', '');
        }
    });

    // ========== Handle Reactions ==========

    $(document).on('click', '.react-btn', function(e) {
        e.stopPropagation(); // Prevent card click (modal open)
        let memeId = $(this).data('id');
        let type = $(this).data('type');
        $.post('php/react.php', { meme_id: memeId, reaction_type: type }, function(data) {
            if (data.success) {
                fetchMemes();
            } else {
                alert(data.message);
            }
        }, 'json');
    });

    // ========== Handle Share ==========

    $(document).on('click', '.share-btn', function(e) {
        e.stopPropagation(); // Prevent card click (modal open)
        let imgPath = window.location.origin + '/' + $(this).data('img').replace(/^\/+/, '');
        let shareData = {
            title: 'Check out this meme!',
            text: 'Look at this meme I found on ZedMemes:',
            url: imgPath
        };

        if (navigator.share) {
            navigator.share(shareData)
                .then(() => {
                    $(this).text('✔️ Shared!');
                    setTimeout(() => {
                        $(this).html('🔗 Share');
                    }, 1500);
                })
                .catch(() => {
                    navigator.clipboard.writeText(imgPath);
                    $(this).text('✔️ Copied!');
                    setTimeout(() => {
                        $(this).html('🔗 Share');
                    }, 1500);
                });
        } else {
            navigator.clipboard.writeText(imgPath);
            $(this).text('✔️ Copied!');
            setTimeout(() => {
                $(this).html('🔗 Share');
            }, 1500);
        }
    });

});