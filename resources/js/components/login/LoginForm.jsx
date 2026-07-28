import React, { useState } from "react";
import { FiMail, FiLock } from "react-icons/fi";
import { useNavigate } from "react-router-dom";
import { loginUser } from "../../services/authService";
import '../../styles/LoginForm.css';

const LoginForm = ({ mode }) => {
  const navigate = useNavigate();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!email || !password) {
      setError("Please enter email and password.");
      return;
    }
    setLoading(true);
    setError("");
    try {
      await loginUser({ email, password }, mode);
      if (mode === "admin") {
        navigate("/admin-dashboard");
      } else {
        navigate("/secretary-dashboard");
      }
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="right-panel">
      <div className="form-container">
        <h2 className="login-title">
          {mode === "admin" ? "Admin Login" : "Secretary Login"}
        </h2>

        {error && <div className="login-error" style={{ color: "red", marginBottom: "10px", textAlign: "center" }}>{error}</div>}

        <div className="login-input-wrapper">
          <FiMail className="login-input-icon" />
          <input
            type="email"
            placeholder="Enter your email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />
        </div>

        <div className="login-input-wrapper">
          <FiLock className="login-input-icon" />
          <input
            type="password"
            placeholder="Enter your password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
          />
        </div>

        <button
          className="login-btn"
          onClick={handleLogin}
          disabled={loading}
        >
          {loading ? "Logging in..." : "Login"}
        </button>
      </div>
    </div>
  );
};

export default LoginForm;