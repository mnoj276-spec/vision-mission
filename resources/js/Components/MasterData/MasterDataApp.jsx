import React, { useState } from 'react';
import CategoryManager from './CategoryManager';
import DepartmentManager from './DepartmentManager';
import QualificationManager from './QualificationManager';
import StateManager from './StateManager';

export default function MasterDataApp() {
    const [activeTab, setActiveTab] = useState('categories');

    const renderActiveTab = () => {
        switch (activeTab) {
            case 'categories': return <CategoryManager />;
            case 'departments': return <DepartmentManager />;
            case 'qualifications': return <QualificationManager />;
            case 'states': return <StateManager />;
            default: return <CategoryManager />;
        }
    };

    return (
        <div className="react-master-data-container">
            <h2 style={{ fontFamily: 'Outfit', fontSize: '1.75rem', marginBottom: '1.5rem' }}>Master Data Management Center</h2>

            {/* Segment Master Tabs */}
            <div className="sub-tab-headers">
                <button 
                    onClick={() => setActiveTab('categories')}
                    className={`sub-tab-btn ${activeTab === 'categories' ? 'active' : ''}`}
                >
                    Job Categories
                </button>
                <button 
                    onClick={() => setActiveTab('departments')}
                    className={`sub-tab-btn ${activeTab === 'departments' ? 'active' : ''}`}
                >
                    Departments
                </button>
                <button 
                    onClick={() => setActiveTab('qualifications')}
                    className={`sub-tab-btn ${activeTab === 'qualifications' ? 'active' : ''}`}
                >
                    Qualifications
                </button>
                <button 
                    onClick={() => setActiveTab('states')}
                    className={`sub-tab-btn ${activeTab === 'states' ? 'active' : ''}`}
                >
                    States/Regions
                </button>
            </div>

            <div className="master-sub-panel">
                {renderActiveTab()}
            </div>
        </div>
    );
}
