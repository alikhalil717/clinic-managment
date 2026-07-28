import API from "../api/axios";

// Dashboard
export const getDashboardStats = async () => {
  const response = await API.get("/admin/dashboard");
  return response.data;
};

// Doctors
export const getDoctors = async () => {
  const response = await API.get("/admin/doctors");
  return response.data;
};

export const getDoctor = async (id) => {
  const response = await API.get(`/admin/doctors/${id}`);
  return response.data;
};

export const createDoctor = async (data) => {
  const response = await API.post("/admin/doctors", data);
  return response.data;
};

export const updateDoctor = async (id, data) => {
  const response = await API.put(`/admin/doctors/${id}`, data);
  return response.data;
};

export const deleteDoctor = async (id) => {
  const response = await API.delete(`/admin/doctors/${id}`);
  return response.data;
};

// Patients
export const getPatients = async () => {
  const response = await API.get("/admin/patients");
  return response.data;
};

export const getPatient = async (id) => {
  const response = await API.get(`/admin/patients/${id}`);
  return response.data;
};

export const createPatient = async (data) => {
  const response = await API.post("/admin/patients", data);
  return response.data;
};

export const updatePatient = async (id, data) => {
  const response = await API.put(`/admin/patients/${id}`, data);
  return response.data;
};

export const deletePatient = async (id) => {
  const response = await API.delete(`/admin/patients/${id}`);
  return response.data;
};

// Appointments
export const getAppointments = async () => {
  const response = await API.get("/admin/appointments");
  return response.data;
};

export const getAppointment = async (id) => {
  const response = await API.get(`/admin/appointments/${id}`);
  return response.data;
};

// Treatment Plans
export const getTreatmentPlans = async () => {
  const response = await API.get("/admin/treatment-plans");
  return response.data;
};

export const getTreatmentPlan = async (id) => {
  const response = await API.get(`/admin/treatment-plans/${id}`);
  return response.data;
};

// Profile
export const getProfile = async () => {
  const response = await API.get("/admin/profile");
  return response.data;
};

export const updateProfile = async (data) => {
  const response = await API.post("/admin/update-profile", data);
  return response.data;
};
