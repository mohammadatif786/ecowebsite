<template>
    <section>
        <div class="section-header">
            <h2>Events Summary</h2>
            <a href="#" class="section-link">My events</a>
        </div>
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div>
                    <div class="summary-card-value">3</div>
                    <div class="summary-card-label">Events added</div>
                </div>
                <div class="summary-card-icon">📅</div>
            </div>
            <div class="summary-card">
                <div>
                    <div class="summary-card-value">3</div>
                    <div class="summary-card-label">Published events</div>
                </div>
                <div class="summary-card-icon">📅</div>
            </div>
            <div class="summary-card">
                <div>
                    <div class="summary-card-value">3</div>
                    <div class="summary-card-label">Upcoming events</div>
                </div>
                <div class="summary-card-icon">📅</div>
            </div>
            <div class="summary-card">
                <div>
                    <div class="summary-card-value">5</div>
                    <div class="summary-card-label">Event dates</div>
                </div>
                <div class="summary-card-icon">📅</div>
            </div>
        </div>

        <!-- Events Table -->
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Sales</th>
                    <th>Status</th>
                    <th>Attendance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Row: Adventure In The Jungle -->
                <tr data-event="adventure">
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <img src="https://via.placeholder.com/48x48" alt="" style="width: 48px; height: 48px; border-radius: 4px;">
                            <div>
                                <a href="#" class="event-name">Adventure In The Jungle</a>
                                <div class="event-id">#cc3081594e</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: bold; margin-bottom: 5px;">30%</div>
                        <div class="sales-progress">
                            <div class="sales-progress-bar" style="width: 30%;"></div>
                        </div>
                        <div class="sales-text">2322 ticket(s) sold</div>
                    </td>
                    <td>
                        <span class="status-badge status-on-sale">On sale</span>
                    </td>
                    <td>
                        <svg width="48" height="48" viewBox="0 0 36 36" class="attendance-chart">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e5e7eb" stroke-width="3"></path>
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831" fill="none" stroke="#4CAF50" stroke-width="3" stroke-dasharray="86,100"></path>
                            <text x="18" y="20" font-size="8" text-anchor="middle" fill="#374151">86%</text>
                        </svg>
                    </td>
                    <td class="more-options">⋯
                        <div class="options-menu">
                            <ul>
                                <li class="stats-option"><span style="font-size: 0.8em;">📈</span> Statistics</li>
                                <li class="payout-option"><span style="font-size: 0.8em;">💵</span> Request payout</li>
                                <li class="details-option"><span style="font-size: 0.8em;">📄</span> Details</li>
                                <li><span style="font-size: 0.8em;">✏️</span> Edit</li>
                                <li><span style="font-size: 0.8em;">👥</span> <a href="attendee.html">Attendees</a></li>
                                <li><span style="font-size: 0.8em;">⭐</span> Reviews</li>
                                <li><span style="font-size: 0.8em;">👁️‍🗨️</span> Draft</li>
                                <li><span style="font-size: 0.8em;">❌</span> Delete</li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <!-- Row: SuperPower -->
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <img src="https://via.placeholder.com/48x48" alt="" style="width: 48px; height: 48px; border-radius: 4px;">
                            <div>
                                <a href="#" class="event-name">SuperPower</a>
                                <div class="event-id">#1a948edf40</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: bold; margin-bottom: 5px;">27%</div>
                        <div class="sales-progress">
                            <div class="sales-progress-bar" style="width: 27%;"></div>
                        </div>
                        <div class="sales-text">2675 ticket(s) sold</div>
                    </td>
                    <td>
                        <span class="status-badge status-on-sale">On sale</span>
                    </td>
                    <td>
                        <svg width="48" height="48" viewBox="0 0 36 36" class="attendance-chart">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 1 1 0 31.831 a 15.9155 15.9155 0 1 1 0 -31.831" fill="none" stroke="#e5e7eb" stroke-width="3"></path>
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831" fill="none" stroke="#4CAF50" stroke-width="3" stroke-dasharray="41,100"></path>
                            <text x="18" y="20" font-size="8" text-anchor="middle" fill="#374151">41%</text>
                        </svg>
                    </td>
                    <td class="more-options">⋯
                        <div class="options-menu">
                            <ul>
                                <li><span style="font-size: 0.8em;">📈</span> Statistics</li>
                                <li><span style="font-size: 0.8em;">💵</span> Request payout</li>
                                <li><span style="font-size: 0.8em;">📄</span> Details</li>
                                <li><span style="font-size: 0.8em;">✏️</span> Edit</li>
                                <li><span style="font-size: 0.8em;">👥</span> Attendees</li>
                                <li><span style="font-size: 0.8em;">⭐</span> Reviews</li>
                                <li><span style="font-size: 0.8em;">👁️‍🗨️</span> Draft</li>
                                <li><span style="font-size: 0.8em;">❌</span> Delete</li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <!-- Row: Gun Man -->
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <img src="https://via.placeholder.com/48x48" alt="" style="width: 48px; height: 48px; border-radius: 4px;">
                            <div>
                                <a href="#" class="event-name">Gun Man</a>
                                <div class="event-id">#bd755e244b</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: bold; margin-bottom: 5px;">100%</div>
                        <div class="sales-progress">
                            <div class="sales-progress-bar" style="width: 100%; background: gray;"></div>
                        </div>
                        <div class="sales-text">4999 ticket(s) sold</div>
                    </td>
                    <td>
                        <span class="status-badge status-sold">Sold</span>
                    </td>
                    <td>
                        <svg width="48" height="48" viewBox="0 0 36 36" class="attendance-chart">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 1 1 0 31.831 a 15.9155 15.9155 0 1 1 0 -31.831" fill="none" stroke="#e5e7eb" stroke-width="3"></path>
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831" fill="none" stroke="#F44336" stroke-width="3" stroke-dasharray="16,100"></path>
                            <text x="18" y="20" font-size="8" text-anchor="middle" fill="#374151">16%</text>
                        </svg>
                    </td>
                    <td class="more-options">⋯
                        <div class="options-menu">
                            <ul>
                                <li><span style="font-size: 0.8em;">📈</span> Statistics</li>
                                <li><span style="font-size: 0.8em;">💵</span> Request payout</li>
                                <li><span style="font-size: 0.8em;">📄</span> Details</li>
                                <li><span style="font-size: 0.8em;">✏️</span> Edit</li>
                                <li><span style="font-size: 0.8em;">👥</span> Attendees</li>
                                <li><span style="font-size: 0.8em;">⭐</span> Reviews</li>
                                <li><span style="font-size: 0.8em;">👁️‍🗨️</span> Draft</li>
                                <li><span style="font-size: 0.8em;">❌</span> Delete</li>
                            </ul>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>
</template>
