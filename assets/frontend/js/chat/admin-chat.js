import * as main from './main.js';
main.chatIcon.addEventListener("click", () => {
    main.chatBox.style.display = "block";
    main.chatIcon.style.display = "none";
    main.messages.scrollTop = main.messages.scrollHeight;
    if (main.roomId) {
        main.adminFetchMessages(main.roomId, main.chatNickname, main.activeContact);
    }
    main.setChatState();
});

main.fetchChatList();
