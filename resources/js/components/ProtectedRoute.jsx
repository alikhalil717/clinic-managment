import React from 'react';
import { Navigate } from 'react-router-dom';
import { isAuthenticated, getUserRole } from '../services/authService';

const ProtectedRoute = ({ children, allowedRole }) => {
  // 1. إذا لم يكن مسجل الدخول، أعد توجيهه إلى صفحة تسجيل الدخول
  if (!isAuthenticated()) {
    return <Navigate to="/" replace />;
  }

  // 2. إذا كان مسجل الدخول ولكن يحاول الدخول لصفحة ليست من صلاحياته
  const currentRole = getUserRole();
  if (allowedRole && currentRole !== allowedRole) {
    // توجيهه للوحة التحكم الخاصة به
    return <Navigate to={currentRole === 'admin' ? '/admin-dashboard' : '/secretary-dashboard'} replace />;
  }

  // 3. إذا كان كل شيء سليم، اعرض الصفحة المطلوبة
  return children;
};

export default ProtectedRoute;