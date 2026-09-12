import axios from "axios";

export function initializeOneSignal() {

    OneSignal.push(() => {
        console.log('running service...');
        if (!window.OneSignalInitialized) {
            OneSignal.init({
                appId: import.meta.env.VITE_ONESIGNAL_APP_ID,
                allowLocalhostAsSecureOrigin: true,
                notifyButton: {
                    enable: true,
                },
            });
            window.OneSignalInitialized = true;
          }
    });
  }

OneSignal.on('subscriptionChange', async function (isSubscribed) {
    console.log('testing...', isSubscribed);
    if (isSubscribed) {
        const playerId = await OneSignal.getUserId();
        axios.post('/save-player-id', {
            player_id: playerId
        }).then((response) => {
            console.log(response);
        });
    }
});

OneSignal.showSlidedownPrompt();

OneSignal.isPushNotificationsEnabled().then((isEnabled) => {
    console.log("Push notifications enabled?", isEnabled);
});

OneSignal.getUserId().then((userId) => {
    console.log("User ID:", userId);
});

OneSignal.on('notificationPermissionChange', (permissionChange) => {
    console.log('Permission changed:', permissionChange);
});








