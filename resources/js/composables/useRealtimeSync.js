import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

/**
 * Composable for real-time data synchronization with polling fallback
 * @param {Function} fetchCallback - Function to fetch data
 * @param {Number} interval - Polling interval in milliseconds (default: 10000)
 * @param {Boolean} immediate - Whether to fetch immediately on mount (default: true)
 */
export function useRealtimeSync(fetchCallback, interval = 10000, immediate = true) {
    const data = ref(null);
    const error = ref(null);
    const loading = ref(false);
    const intervalId = ref(null);

    const fetch = async () => {
        loading.value = true;
        error.value = null;

        try {
            const result = await fetchCallback();
            data.value = result;
            return result;
        } catch (err) {
            error.value = err;
            console.error('Realtime sync error:', err);
            return null;
        } finally {
            loading.value = false;
        }
    };

    const startPolling = () => {
        if (intervalId.value) return; // Already polling

        intervalId.value = setInterval(async () => {
            await fetch();
        }, interval);
    };

    const stopPolling = () => {
        if (intervalId.value) {
            clearInterval(intervalId.value);
            intervalId.value = null;
        }
    };

    onMounted(() => {
        if (immediate) {
            fetch();
        }
        startPolling();
    });

    onUnmounted(() => {
        stopPolling();
    });

    return {
        data,
        error,
        loading,
        fetch,
        startPolling,
        stopPolling,
    };
}

/**
 * Composable for viewer tracking with heartbeat
 */
export function useViewerTracking(streamId) {
    const heartbeatInterval = ref(null);
    const isTracking = ref(false);

    const startTracking = async () => {
        if (isTracking.value || !streamId) return;

        try {
            // Send join event
            const response = await axios.post(route('frontend.live.viewer.join', { stream: streamId }));
            if (response.data.message === 'Host is not counted as viewer') {
                return;
            }

            isTracking.value = true;

            // Start heartbeat every 20 seconds
            heartbeatInterval.value = setInterval(async () => {
                try {
                    const heartbeatResponse = await axios.post(route('frontend.live.viewer.heartbeat', { stream: streamId }));
                    if (heartbeatResponse.data.message === 'Host heartbeat not tracked') {
                        stopTracking();
                    }
                } catch (error) {
                    console.error('Heartbeat error:', error);
                }
            }, 20000);
        } catch (error) {
            console.error('Failed to start viewer tracking:', error);
        }
    };

    const stopTracking = async () => {
        if (!isTracking.value || !streamId) return;

        try {
            // Send leave event
            const response = await axios.post(route('frontend.live.viewer.leave', { stream: streamId }));

            // Handle host exclusion gracefully
            if (response.data.message === 'Host leaving not tracked') {
                console.log('Host detected - not tracking leave event');
            }
        } catch (error) {
            console.error('Failed to stop viewer tracking:', error);
        }

        // Clear heartbeat
        if (heartbeatInterval.value) {
            clearInterval(heartbeatInterval.value);
            heartbeatInterval.value = null;
        }

        isTracking.value = false;
    };

    onUnmounted(() => {
        stopTracking();
    });

    return {
        isTracking,
        startTracking,
        stopTracking,
    };
}

/**
 * Composable for fetching live stream statistics
 */
export function useStreamStats(streamId, interval = 10000) {
    const fetchStats = async () => {
        if (!streamId) return null;

        try {
            const response = await axios.get(route('frontend.live.stats', { stream: streamId }));
            return response.data;
        } catch (error) {
            console.error('Failed to fetch stream stats:', error);
            return null;
        }
    };

    return useRealtimeSync(fetchStats, interval, true);
}
