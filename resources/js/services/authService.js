import API from "../api/axios";

// دالة تسجيل الدخول
export const loginUser = async (credentials) => {
  try {
    const response = await API.post("/login", credentials);
    
    // إذا كان الباك اند يرجع Token عند النجاح
    if (response.data && response.data.token) {
      // تخزين التوكن في المتصفح (LocalStorage)
      localStorage.setItem("authToken", response.data.token);
      
      // تخزين دور المستخدم (أدمن أو سكرتاريا) للرجوع إليه لاحقاً
      // افترضنا أن الباك اند يرجع نوع المستخدم، يمكنك تعديل "role" حسب استجابة الـ API الخاص بك
      if (response.data.role) {
        localStorage.setItem("userRole", response.data.role); 
      }
    }
    
    return response.data;
  } catch (error) {
    // تنسيق الخطأ لتسهيل عرضه في واجهة المستخدم (LoginForm)
    const errorMessage = error.response?.data?.message || "حدث خطأ أثناء تسجيل الدخول، يرجى المحاولة مرة أخرى.";
    throw new Error(errorMessage);
  }
};

// دالة تسجيل الخروج
export const logoutUser = async () => {
  try {
    // إرسال طلب للباك اند لإلغاء صلاحية التوكن الحالي (اختياري ومهم للأمان)
    await API.post("/logout");
  } catch (error) {
    console.error("Error during logout API call", error);
  } finally {
    // الأهم: مسح البيانات من المتصفح في جميع الأحوال
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