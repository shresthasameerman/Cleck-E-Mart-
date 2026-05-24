        <section id="collection" class="tab-content" style="display: none;">
            <div class="admin-panel" style="max-width: 800px; margin: 0 auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <div>
                        <h3 style="margin: 0;">Collection Verification</h3>
                        <p style="margin: 0.25rem 0 0; color: var(--color-muted); font-size: 0.9rem;">Scan a customer's RFID card to pull up their orders.</p>
                    </div>
                    <button id="rfidConnectBtn" class="button" style="display: inline-flex; align-items: center; gap: 0.5rem; background: var(--color-primary-dark); color: white;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        Connect Scanner
                    </button>
                </div>

                <div id="rfidStatus" style="padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--radius-sm); background: #f3f4f6; color: #4b5563; font-size: 0.95rem; border: 1px solid #e5e7eb; display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #9ca3af;"></div>
                    Scanner disconnected. Click 'Connect Scanner' to start.
                </div>

                <div id="rfidLoading" style="display: none; padding: 3rem; text-align: center; color: var(--color-muted);">
                    <svg class="spinner" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite;"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>
                    <p style="margin-top: 1rem;">Looking up customer...</p>
                </div>

                <div id="rfidResults" style="display: none;">
                    <div style="background: white; border: 1px solid rgba(0,0,0,0.1); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 1.5rem;">
                        <h4 style="margin: 0 0 1rem 0; font-size: 1.1rem; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.5rem;">Customer Details</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <span style="font-size: 0.8rem; color: var(--color-muted); text-transform: uppercase;">Name</span>
                                <div id="rfidCustName" style="font-weight: 600; font-size: 1.1rem;">-</div>
                            </div>
                            <div>
                                <span style="font-size: 0.8rem; color: var(--color-muted); text-transform: uppercase;">Email</span>
                                <div id="rfidCustEmail">-</div>
                            </div>
                            <div>
                                <span style="font-size: 0.8rem; color: var(--color-muted); text-transform: uppercase;">Phone</span>
                                <div id="rfidCustPhone">-</div>
                            </div>
                        </div>
                    </div>

                    <h4 style="margin: 0 0 1rem 0; font-size: 1.1rem;">Ready for Collection</h4>
                    <div id="rfidOrdersList">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>
        </section>
