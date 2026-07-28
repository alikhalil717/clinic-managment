import API from "../api/axios";

// دالة تسجيل الدخول
export const loginUser = async (credentials, role) => {
  try {
    const endpoint = role === "admin" ? "/admin/login" : "/secretary/login";
    const response = await API.post(endpoint, credentials);

    if (response.data && response.data.token) {
      localStorage.setItem("authToken", response.data.token);
      // Extract role from the user object in the response, or use the passed role
      const userRole = response.data.user?.role || role;
      localStorage.setItem("userRole", userRole.toLowerCase());
    }

    return response.data;
  } catch (error) {
    const errorMessage = error.response?.data?.message || "An error occurred during login.";
    throw new Error(errorMessage);
  }
};

// دالة تسجيل الخروج
export const logoutUser = async (role) => {
  try {
    const endpoint = role === "admin" ? "/admin/logout" : "/secretary/logout";
    await API.post(endpoint);
  } catch (error) {
    console.error("Error during logout API call", error);
  } finally {
    localStorage.removeItem("authToken");
    localStorage.removeItem("userRole");
  }
};

// دالة للتحقق مما إذا كان المستخدم مسجل دخوله حالياً
export const isAuthenticated = () => {
  return !!localStorage.getItem("authToken");
};

// دالة لجلب دور المستخدم الحالي (لتحديد أي داشبورد يجب عرضها)
export const getUserRole = () => {
  return localStorage.getItem("userRole");
};