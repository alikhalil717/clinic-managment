import React from "react";
import { FiMail, FiLock } from "react-icons/fi";
import { useNavigate } from "react-router-dom";
import '../../styles/LoginForm.css';

const LoginForm = ({ mode }) => {
  const navigate = useNavigate();

  const handleLogin = () => {
    // مسار التوجيه الخاص بك بقي كما هو دون أي تغيير
    if (mode === "admin") {
      navigate("/admin-dashboard");
    } else {
      navigate("/secretary-dashboard");
    }
  };

  return (
    <div className="right-panel">
      <div className="form-container">
        <h2 className="login-title">
          {mode === "admin" ? "Admin Login" : "Secretary Login"}
        </h2>

        {/* الحقل الأول: الإيميل */}
        <div className="login-input-wrapper">
          <FiMail className="login-input-icon" />
          <input
            type="email"
            placeholder="Enter your email"
          />
        </div>

        {/* الحقل الثاني: كلمة المرور */}
        <div className="login-input-wrapper">
          <FiLock className="login-input-icon" />
          <input
            type="password"
            placeholder="Enter your password"
          />
        </div>

        <button
          className="login-btn"
          onClick={handleLogin}
        >
          Login
        </button>
      </div>
    </div>
  );
};

export default LoginForm;