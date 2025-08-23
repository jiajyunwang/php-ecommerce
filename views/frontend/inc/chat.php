<?php if (isset($_SESSION['email'])): ?>
    <?php if ($user['role'] === 'user'): ?>
        <div id="chat-widget">
            <div id="chat-icon" class="chat-icon">
                <div class="unread unread-total">
                    <div class="unread-count"></div>
                </div>
                &#128172;
            </div>
            <div id="chat-box" class="chat-box" data-user-id="<?= $user['id'] ?>">
                <div id="chat-header">
                    <span>即時對話</span>
                    <button id="close-chat">&times;</button>
                </div>
                <div id="chat-main">
                    <div id="hidden" class="chat-list">
                        <ul id="contacts"></ul>
                    </div>
                    <div id="chat-content" class="chat-content">
                        <div id="messages"></div>
                        <div class="chat-input">
                            <input type="text" id="message-input" placeholder="輸入訊息...">
                            <button id="send-message">送出</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div id="chat-widget">
            <div id="chat-icon"  class="chat-icon">
                <div class="unread unread-total">
                    <div class="unread-count"></div>
                </div>
                &#128172;
            </div>
            <div id="admin-chat-box" class="chat-box">
                <div id="chat-header">
                    <span>即時對話</span>
                    <button id="close-chat">&times;</button>
                </div>
                <div id="chat-main">
                    <div id="chat-list" class="chat-list">
                        <ul id="contacts"></ul>
                    </div>
                    <div id="chat-content" class="admin-chat-content">
                        <div id="hidden" class="chat-content-header"></div>
                        <div id="messages"></div>
                        <div id="hidden" class="chat-input">
                            <input type="text" id="message-input" placeholder="輸入訊息...">
                            <button id="send-message">送出</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>