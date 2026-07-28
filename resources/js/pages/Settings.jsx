import React from "react";
import Sidebar from "../components/dashboard/Sidebar";
import Topbar from "../components/Topbar";
import { FiSave, FiUser, FiSliders, FiBell } from "react-icons/fi";
import "../styles/dashboard.css";
import "../styles/Settings.css";

export default function Settings() {
  return (
    <div className="dashboard">
      <Sidebar role="admin" />
      <div className="main-content">
        <Topbar title="System Settings" />
        
        <div className="settings-page-centered" style={{ marginTop: "20px" }}>
          <div className="unified-settings-card">
            
            {/* الترويسة العلوية للبطاقة */}
            <div className="settings-header-banner">
              <h3>General Settings</h3>
              <p>Update your clinic's basic information and system preferences.</p>
            </div>

            <div className="settings-body">
              
              {/* القسم الأول: معلومات العيادة */}
              <div className="settings-section">
                <div className="section-title">
                  <FiUser size={18} /> Clinic Information
                </div>
                <div className="form-grid">
                  <div className="input-group">
                    <label>Clinic Name</label>
                    <input type="text" defaultValue="DentaPrint Specialty Center" />
                  </div>
                  <div className="input-group">
                    <label>Phone Number</label>
                    <input type="tel" defaultValue="+1 234 567 890" />
                  </div>
                  <div className="input-group">
                    <label>Email Address</label>
                    <input type="email" defaultValue="admin@dentaprint.com" />
                  </div>
                  <div className="input-group">
                    <label>Location / Address</label>
                    <input type="text" defaultValue="123 Dental Street, Medical District" />
                  </div>
                </div>
              </div>

              <div className="divider"></div>

              {/* القسم الثاني: التفضيلات */}
              <div className="settings-section">
                <div className="section-title">
                  <FiSliders size={18} /> Preferences
                </div>
                <div className="form-grid">
                  <div className="input-group">
                    <label>Currency</label>
                    <select defaultValue="USD">
                      <option value="USD">USD ($)</option>
                      <option value="EUR">EUR (€)</option>
                      <option value="SAR">SAR (ر.س)</option>
                    </select>
                  </div>
                  <div className="input-group">
                    <label>Time Zone</label>
                    <select defaultValue="AST">
                      <option value="AST">Arabia Standard Time (UTC+3)</option>
                      <option value="GMT">Greenwich Mean Time (UTC+0)</option>
                    </select>
                  </div>
                </div>
              </div>

              <div className="divider"></div>

              {/* القسم الثالث: الإشعارات */}
              <div className="settings-section">
                <div className="section-title">
                  <FiBell size={18} /> Notifications & Alerts
                </div>
                <div className="unified-toggle-row">
                  <div className="unified-toggle-text">
                    <h5>SMS Reminders</h5>
                    <p>Send automated text messages to patients before their appointments.</p>
                  </div>
                  <label className="toggle-switch">
                    <input type="checkbox" defaultChecked />
                    <span className="toggle-slider"></span>
                  </label>
                </div>
                <div className="unified-toggle-row">
                  <div className="unified-toggle-text">
                    <h5>Email Confirmations</h5>
                    <p>Send an email receipt when a patient books online or completes a visit.</p>
                  </div>
                  <label className="toggle-switch">
                    <input type="checkbox" defaultChecked />
                    <span className="toggle-slider"></span>
                  </label>
                </div>
              </div>

            </div>

            {/* زر الحفظ مدمج بأسفل البطاقة */}
            <div className="save-footer">
              <button className="btn-primary" style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '10px 25px', borderRadius: '8px', fontWeight: 'bold' }}>
                <FiSave /> Save Changes
              </button>
            </div>

          </div>
        </div>
        
      </div>
    </div>
  );
}