import React from "react";
import Sidebar from "../components/dashboard/Sidebar";
import Topbar from "../components/Topbar";
import { FiUsers, FiDollarSign, FiCalendar, FiActivity } from "react-icons/fi";
// 👈 استيراد مكونات المخطط البياني من مكتبة recharts
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from "recharts"; 
import "../styles/dashboard.css";
import "../styles/Reports.css";

// 👈 بيانات وهمية لأرباح العيادة خلال أشهر السنة
const revenueData = [
  { name: 'Jan', revenue: 15000 },
  { name: 'Feb', revenue: 18000 },
  { name: 'Mar', revenue: 16000 },
  { name: 'Apr', revenue: 22000 },
  { name: 'May', revenue: 20000 },
  { name: 'Jun', revenue: 24500 },
  { name: 'Jul', revenue: 23000 },
  { name: 'Aug', revenue: 27000 },
  { name: 'Sep', revenue: 25000 },
  { name: 'Oct', revenue: 29000 },
  { name: 'Nov', revenue: 31000 },
  { name: 'Dec', revenue: 34000 },
];

export default function Reports() {
  return (
    <div className="dashboard">
      <Sidebar role="admin" />

      <div className="main-content">
        <Topbar title="Clinic Reports & Analytics" />

        <div className="reports-page" style={{ marginTop: "30px" }}>
          
          <div className="stats-container">
            <div className="stat-card">
              <div className="stat-icon" style={{ backgroundColor: '#e0e7ff', color: '#4f46e5' }}>
                <FiDollarSign />
              </div>
              <div className="stat-details">
                <p>Total Revenue (This Month)</p>
                <h3>$24,500</h3>
                <span style={{ color: '#22c55e', fontSize: '12px', fontWeight: 'bold' }}>+15% from last month</span>
              </div>
            </div>

            <div className="stat-card">
              <div className="stat-icon" style={{ backgroundColor: '#dcfce7', color: '#16a34a' }}>
                <FiUsers />
              </div>
              <div className="stat-details">
                <p>New Patients</p>
                <h3>142</h3>
                <span style={{ color: '#22c55e', fontSize: '12px', fontWeight: 'bold' }}>+8% from last month</span>
              </div>
            </div>

            <div className="stat-card">
              <div className="stat-icon" style={{ backgroundColor: '#fef08a', color: '#ca8a04' }}>
                <FiCalendar />
              </div>
              <div className="stat-details">
                <p>Total Appointments</p>
                <h3>320</h3>
                <span style={{ color: '#64748b', fontSize: '12px' }}>Across all doctors</span>
              </div>
            </div>
          </div>

          <div className="charts-overview-container" style={{ display: 'flex', gap: '20px', marginTop: '20px' }}>
            
            {/* قسم المخطط البياني */}
            <div style={{ flex: '2', backgroundColor: 'white', padding: '20px', borderRadius: '12px', border: '1px solid #e2e8f0' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
                <h3 style={{ margin: 0, color: '#0f172a' }}>Monthly Revenue Overview</h3>
                <select className="search-input" style={{ padding: '5px 10px', borderRadius: '6px' }}>
                  <option>2026</option>
                  <option>2025</option>
                </select>
              </div>
              
              {/* 👈 المخطط التفاعلي الحقيقي */}
              <div style={{ height: "280px", width: "100%", marginTop: "10px" }}>
                <ResponsiveContainer width="100%" height="100%">
                  <LineChart data={revenueData} margin={{ top: 5, right: 20, bottom: 5, left: 0 }}>
                    <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e2e8f0" />
                    <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fill: '#64748b', fontSize: 12 }} dy={10} />
                    <YAxis axisLine={false} tickLine={false} tick={{ fill: '#64748b', fontSize: 12 }} tickFormatter={(value) => `$${value/1000}k`} dx={-10} />
                    <Tooltip 
                      contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 15px rgba(0,0,0,0.1)' }}
                      formatter={(value) => [`$${value}`, "Revenue"]}
                    />
                    <Line type="monotone" dataKey="revenue" stroke="#3182ce" strokeWidth={3} dot={{ r: 4, fill: '#3182ce', strokeWidth: 2, stroke: '#fff' }} activeDot={{ r: 6 }} />
                  </LineChart>
                </ResponsiveContainer>
              </div>
            </div>

            <div style={{ flex: '1', backgroundColor: 'white', padding: '20px', borderRadius: '12px', border: '1px solid #e2e8f0' }}>
              <h3 style={{ margin: 0, marginBottom: '20px', color: '#0f172a' }}>Most Requested Services</h3>
              
              <div style={{ display: 'flex', flexDirection: 'column', gap: '15px' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <span style={{ display: 'flex', alignItems: 'center', gap: '8px', color: '#334155' }}><FiActivity color="#3182ce"/> Teeth Cleaning</span>
                  <span style={{ fontWeight: 'bold' }}>45%</span>
                </div>
                <div style={{ width: '100%', backgroundColor: '#e2e8f0', height: '8px', borderRadius: '4px' }}>
                  <div style={{ width: '45%', backgroundColor: '#3182ce', height: '100%', borderRadius: '4px' }}></div>
                </div>

                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: '10px' }}>
                  <span style={{ display: 'flex', alignItems: 'center', gap: '8px', color: '#334155' }}><FiActivity color="#805ad5"/> Root Canal</span>
                  <span style={{ fontWeight: 'bold' }}>30%</span>
                </div>
                <div style={{ width: '100%', backgroundColor: '#e2e8f0', height: '8px', borderRadius: '4px' }}>
                  <div style={{ width: '30%', backgroundColor: '#805ad5', height: '100%', borderRadius: '4px' }}></div>
                </div>

                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: '10px' }}>
                  <span style={{ display: 'flex', alignItems: 'center', gap: '8px', color: '#334155' }}><FiActivity color="#38a169"/> Orthodontics</span>
                  <span style={{ fontWeight: 'bold' }}>25%</span>
                </div>
                <div style={{ width: '100%', backgroundColor: '#e2e8f0', height: '8px', borderRadius: '4px' }}>
                  <div style={{ width: '25%', backgroundColor: '#38a169', height: '100%', borderRadius: '4px' }}></div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  );
}