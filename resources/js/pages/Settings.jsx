import React, { useState, useEffect } from "react";
import Sidebar from "../components/dashboard/Sidebar";
import Topbar from "../components/Topbar";
import { FiSave, FiUser, FiSliders, FiBell } from "react-icons/fi";
import { getProfile, updateProfile } from "../services/adminService";
import "../styles/dashboard.css";
import "../styles/Settings.css";

export default function Settings() {
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState("");

  useEffect(() => {
    const fetchProfile = async () => {
      try {
        const res = await getProfile();
        setProfile(res.data);
      } catch (err) {
        console.error("Failed to load profile:", err);
      } finally {
        setLoading(false);
      }
    };
    fetchProfile();
  }, []);

  const handleSave = async () => {
    setSaving(true);
    setMessage("");
    try {
      await updateProfile({});
      setMessage("Settings saved successfully!");
    } catch (err) {
      setMessage("Failed to save settings.");
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="dashboard">
        <Sidebar role="admin" />
        <div className="main-content">
          <Topbar title="System Settings" />
          <p style={{ padding: "40px", textAlign: "center" }}>Loading settings...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="dashboard">
      <Sidebar role="admin" />
      <div className="main-content">
        <Topbar title="System Settings" />

        <div className="settings-page-centered" style={{ marginTop: "20px" }}>
          <div className="unified-settings-card">
            <div className="settings-header-banner">
              <h3>General Settings</h3>
              <p>Update your clinic's basic information and system preferences.</p>
            </div>

            {message && (
              <div style={{ padding: "10px 20px", color: message.includes("successfully") ? "green" : "red" }}>
                {message}
              </div>
            )}

            <div className="settings-body">
              <div className="settings-section">
                <div className="section-title">
                  <FiUser size={18} /> Profile Information
                </div>
                <div className="form-grid">
                  <div className="input-group">
                    <label>First Name</label>
                    <input type="text" defaultValue={profile?.user?.first_name || ""} />
                  </div>
                  <div className="input-group">
                    <label>Last Name</label>
                    <input type="text" defaultValue={profile?.user?.last_name || ""} />
                  </div>
                  <div className="input-group">
                    <label>Email Address</label>
                    <input type="email" defaultValue={profile?.user?.email || ""} />
                  </div>
                  <div className="input-group">
                    <label>Phone Number</label>
                    <input type="tel" defaultValue={profile?.user?.phone || ""} />
                  </div>
                </div>
              </div>
            </div>

            <div className="save-footer">
              <button
                className="btn-primary"
                onClick={handleSave}
                disabled={saving}
                style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '10px 25px', borderRadius: '8px', fontWeight: 'bold' }}
              >
                <FiSave /> {saving ? "Saving..." : "Save Changes"}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}