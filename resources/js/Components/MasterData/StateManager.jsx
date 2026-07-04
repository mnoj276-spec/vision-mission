import React, { useState, useEffect } from 'react';
import axios from 'axios';
import DataTable from './DataTable';

export default function StateManager() {
    const [states, setStates] = useState([]);
    const [loading, setLoading] = useState(true);
    const [formState, setFormState] = useState({ id: null, name: '', code: '' });
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    const fetchStates = async () => {
        setLoading(true);
        try {
            const res = await axios.get('/api/admin/states');
            setStates(res.data.data);
        } catch (err) {
            setError('Failed to fetch states.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchStates();
    }, []);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setSuccess('');

        try {
            if (formState.id) {
                await axios.post(`/api/admin/states/${formState.id}`, { name: formState.name, code: formState.code });
                setSuccess('State updated successfully!');
            } else {
                await axios.post('/api/admin/states', { name: formState.name, code: formState.code });
                setSuccess('State created successfully!');
            }
            setFormState({ id: null, name: '', code: '' });
            fetchStates();
        } catch (err) {
            setError(err.response?.data?.message || 'An error occurred.');
        }
    };

    const handleEdit = (stateData) => {
        setFormState({ id: stateData.id, name: stateData.name, code: stateData.code });
        setError('');
        setSuccess('');
    };

    const handleDelete = async (id) => {
        setError('');
        setSuccess('');
        try {
            await axios.delete(`/api/admin/states/${id}`);
            setSuccess('State deleted successfully!');
            fetchStates();
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to delete state.');
        }
    };

    const columns = [
        { header: 'ID', accessor: 'id' },
        { header: 'Code', accessor: 'code' },
        { header: 'State Name', accessor: 'name' },
        { header: 'Slug', accessor: 'slug' },
    ];

    return (
        <div className="grid grid-cols-1 md:grid-cols-[1fr_2fr] gap-8 items-start">
            <div className="glass-panel" style={{ padding: '1.5rem', borderRadius: '16px' }}>
                <h3 style={{ fontFamily: 'Outfit', fontSize: '1.25rem', marginBottom: '1.5rem', color: 'var(--accent-color)' }}>
                    {formState.id ? 'Edit State/Region' : 'Add State/Region'}
                </h3>
                
                {error && <div style={{ marginBottom: '1rem', padding: '0.75rem 1rem', background: 'rgba(239, 68, 68, 0.1)', border: '1px solid rgba(239, 68, 68, 0.2)', color: '#ef4444', borderRadius: '8px', fontSize: '0.85rem' }}>{error}</div>}
                {success && <div style={{ marginBottom: '1rem', padding: '0.75rem 1rem', background: 'rgba(16, 185, 129, 0.1)', border: '1px solid rgba(16, 185, 129, 0.2)', color: '#10b981', borderRadius: '8px', fontSize: '0.85rem' }}>{success}</div>}

                <form onSubmit={handleSubmit}>
                    <div style={{ marginBottom: '1.25rem' }}>
                        <label style={{ display: 'block', fontSize: '0.85rem', fontWeight: '600', color: 'var(--text-secondary)', marginBottom: '0.5rem' }}>State Name</label>
                        <input 
                            type="text" 
                            className="form-control"
                            placeholder="e.g. Maharashtra" 
                            value={formState.name}
                            onChange={(e) => setFormState({ ...formState, name: e.target.value })}
                            required
                        />
                    </div>
                    <div style={{ marginBottom: '1.25rem' }}>
                        <label style={{ display: 'block', fontSize: '0.85rem', fontWeight: '600', color: 'var(--text-secondary)', marginBottom: '0.5rem' }}>State ISO Code</label>
                        <input 
                            type="text" 
                            className="form-control"
                            placeholder="e.g. MH" 
                            value={formState.code}
                            onChange={(e) => setFormState({ ...formState, code: e.target.value })}
                            required
                        />
                    </div>
                    <button type="submit" className="btn-primary" style={{ width: '100%' }}>
                        {formState.id ? 'Update State' : 'Save State'}
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
                    <div className="text-center" style={{ padding: '2rem', color: 'var(--text-secondary)' }}>Loading states...</div>
                ) : (
                    <DataTable 
                        columns={columns} 
                        data={states} 
                        onEdit={handleEdit} 
                        onDelete={handleDelete} 
                    />
                )}
            </div>
        </div>
    );
}
