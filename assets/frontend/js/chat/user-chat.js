import * as main from './main.js';

main.chatIcon.addEventListener("click", () => {
    main.chatBox.style.display = "block";
    main.chatIcon.style.display = "none";
    main.messages.scrollTop = main.messages.scrollHeight;
    main.fetchMessages();
    main.setUserId();
    main.setChatState();
    main.setRole();
    main.messageListen(main.role);
});

main.fetchUnreadCount();

