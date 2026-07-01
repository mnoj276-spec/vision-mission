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
            <h2 className="text-[1.75rem] font-['Outfit'] mb-6">Master Data Management Center</h2>

            {/* Segment Master Tabs */}
            <div className="sub-tab-headers flex gap-2 mb-6 border-b border-gray-200 pb-2">
                <button 
                    onClick={() => setActiveTab('categories')}
                    className={`sub-tab-btn px-4 py-2 font-semibold transition-colors duration-200 border-b-2 ${activeTab === 'categories' ? 'border-[var(--accent-color)] text-[var(--accent-color)]' : 'border-transparent text-gray-500 hover:text-gray-700'}`}
                >
                    Job Categories
                </button>
                <button 
                    onClick={() => setActiveTab('departments')}
                    className={`sub-tab-btn px-4 py-2 font-semibold transition-colors duration-200 border-b-2 ${activeTab === 'departments' ? 'border-[var(--accent-color)] text-[var(--accent-color)]' : 'border-transparent text-gray-500 hover:text-gray-700'}`}
                >
                    Departments
                </button>
                <button 
                    onClick={() => setActiveTab('qualifications')}
                    className={`sub-tab-btn px-4 py-2 font-semibold transition-colors duration-200 border-b-2 ${activeTab === 'qualifications' ? 'border-[var(--accent-color)] text-[var(--accent-color)]' : 'border-transparent text-gray-500 hover:text-gray-700'}`}
                >
                    Qualifications
                </button>
                <button 
                    onClick={() => setActiveTab('states')}
                    className={`sub-tab-btn px-4 py-2 font-semibold transition-colors duration-200 border-b-2 ${activeTab === 'states' ? 'border-[var(--accent-color)] text-[var(--accent-color)]' : 'border-transparent text-gray-500 hover:text-gray-700'}`}
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
