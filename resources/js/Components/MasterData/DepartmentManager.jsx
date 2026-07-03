import React, { useState, useEffect } from 'react';
import axios from 'axios';
import DataTable from './DataTable';

export default function DepartmentManager() {
    const [departments, setDepartments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [formState, setFormState] = useState({ id: null, name: '', code: '' });
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    const fetchDepartments = async () => {
        setLoading(true);
        try {
            const res = await axios.get('/api/admin/departments');
            setDepartments(res.data.data);
        } catch (err) {
            setError('Failed to fetch departments.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchDepartments();
    }, []);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setSuccess('');

        try {
            if (formState.id) {
                await axios.post(`/api/admin/departments/${formState.id}`, { name: formState.name, code: formState.code });
                setSuccess('Department updated successfully!');
            } else {
                await axios.post('/api/admin/departments', { name: formState.name, code: formState.code });
                setSuccess('Department created successfully!');
            }
            setFormState({ id: null, name: '', code: '' });
            fetchDepartments();
        } catch (err) {
            setError(err.response?.data?.message || 'An error occurred.');
        }
    };

    const handleEdit = (dept) => {
        setFormState({ id: dept.id, name: dept.name, code: dept.code });
        setError('');
        setSuccess('');
    };

    const handleDelete = async (id) => {
        setError('');
        setSuccess('');
        try {
            await axios.delete(`/api/admin/departments/${id}`);
            setSuccess('Department deleted successfully!');
            fetchDepartments();
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to delete department.');
        }
    };

    const columns = [
        { header: 'ID', accessor: 'id' },
        { header: 'Code', accessor: 'code' },
        { header: 'Department Name', accessor: 'name' },
        { header: 'Slug', accessor: 'slug' },
    ];

    return (
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 2fr', gap: '2rem', alignItems: 'start' }}>
            <div className="glass-panel" style={{ padding: '1.5rem', borderRadius: '16px' }}>
                <h3 style={{ fontFamily: 'Outfit', fontSize: '1.25rem', marginBottom: '1.5rem', color: 'var(--accent-color)' }}>
                    {formState.id ? 'Edit Department' : 'Add Department'}
                </h3>
                
                {error && <div style={{ marginBottom: '1rem', padding: '0.75rem 1rem', background: 'rgba(239, 68, 68, 0.1)', border: '1px solid rgba(239, 68, 68, 0.2)', color: '#ef4444', borderRadius: '8px', fontSize: '0.85rem' }}>{error}</div>}
                {success && <div style={{ marginBottom: '1rem', padding: '0.75rem 1rem', background: 'rgba(16, 185, 129, 0.1)', border: '1px solid rgba(16, 185, 129, 0.2)', color: '#10b981', borderRadius: '8px', fontSize: '0.85rem' }}>{success}</div>}

                <form onSubmit={handleSubmit}>
                    <div style={{ marginBottom: '1.25rem' }}>
                        <label style={{ display: 'block', fontSize: '0.85rem', fontWeight: '600', color: 'var(--text-secondary)', marginBottom: '0.5rem' }}>Department Name</label>
                        <input 
                            type="text" 
                            className="form-control"
                            placeholder="e.g. Staff Selection Board" 
                            value={formState.name}
                            onChange={(e) => setFormState({ ...formState, name: e.target.value })}
                            required
                        />
                    </div>
                    <div style={{ marginBottom: '1.25rem' }}>
                        <label style={{ display: 'block', fontSize: '0.85rem', fontWeight: '600', color: 'var(--text-secondary)', marginBottom: '0.5rem' }}>Unique Code</label>
                        <input 
                            type="text" 
                            className="form-control"
                            placeholder="e.g. SSC" 
                            value={formState.code}
                            onChange={(e) => setFormState({ ...formState, code: e.target.value })}
                            required
                        />
                    </div>
                    <button type="submit" className="btn-primary" style={{ width: '100%' }}>
                        {formState.id ? 'Update Department' : 'Save Department'}
                    </button>
                    {formState.id && (
                        <button 
                            type="button" 
                            onClick={() => setFormState({ id: null, name: '', code: '' })}
                            className="btn-secondary"
                            style={{ width: '100%', marginTop: '0.5rem' }}
                        >
                            Cancel Edit
                        </button>
                    )}
                </form>
            </div>

            <div>
                {loading ? (
                    <div className="text-center" style={{ padding: '2rem', color: 'var(--text-secondary)' }}>Loading departments...</div>
                ) : (
                    <DataTable 
                        columns={columns} 
                        data={departments} 
                        onEdit={handleEdit} 
                        onDelete={handleDelete} 
                    />
                )}
            </div>
        </div>
    );
}
