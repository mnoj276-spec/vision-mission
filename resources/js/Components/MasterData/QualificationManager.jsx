import React, { useState, useEffect } from 'react';
import axios from 'axios';
import DataTable from './DataTable';

export default function QualificationManager() {
    const [qualifications, setQualifications] = useState([]);
    const [loading, setLoading] = useState(true);
    const [formState, setFormState] = useState({ id: null, name: '' });
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    const fetchQualifications = async () => {
        setLoading(true);
        try {
            const res = await axios.get('/api/admin/qualifications');
            setQualifications(res.data.data);
        } catch (err) {
            setError('Failed to fetch qualifications.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchQualifications();
    }, []);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setSuccess('');

        try {
            if (formState.id) {
                await axios.post(`/api/admin/qualifications/${formState.id}`, { name: formState.name });
                setSuccess('Qualification updated successfully!');
            } else {
                await axios.post('/api/admin/qualifications', { name: formState.name });
                setSuccess('Qualification created successfully!');
            }
            setFormState({ id: null, name: '' });
            fetchQualifications();
        } catch (err) {
            setError(err.response?.data?.message || 'An error occurred.');
        }
    };

    const handleEdit = (qual) => {
        setFormState({ id: qual.id, name: qual.name });
        setError('');
        setSuccess('');
    };

    const handleDelete = async (id) => {
        setError('');
        setSuccess('');
        try {
            await axios.delete(`/api/admin/qualifications/${id}`);
            setSuccess('Qualification deleted successfully!');
            fetchQualifications();
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to delete qualification.');
        }
    };

    const columns = [
        { header: 'ID', accessor: 'id' },
        { header: 'Qualification Name', accessor: 'name' },
        { header: 'Slug', accessor: 'slug' },
    ];

    return (
        <div className="grid grid-cols-1 md:grid-cols-[1fr_2fr] gap-8 items-start">
            <div className="glass-panel" style={{ padding: '1.5rem', borderRadius: '16px' }}>
                <h3 style={{ fontFamily: 'Outfit', fontSize: '1.25rem', marginBottom: '1.5rem', color: 'var(--accent-color)' }}>
                    {formState.id ? 'Edit Qualification' : 'Add Qualification'}
                </h3>
                
                {error && <div style={{ marginBottom: '1rem', padding: '0.75rem 1rem', background: 'rgba(239, 68, 68, 0.1)', border: '1px solid rgba(239, 68, 68, 0.2)', color: '#ef4444', borderRadius: '8px', fontSize: '0.85rem' }}>{error}</div>}
                {success && <div style={{ marginBottom: '1rem', padding: '0.75rem 1rem', background: 'rgba(16, 185, 129, 0.1)', border: '1px solid rgba(16, 185, 129, 0.2)', color: '#10b981', borderRadius: '8px', fontSize: '0.85rem' }}>{success}</div>}

                <form onSubmit={handleSubmit}>
                    <div style={{ marginBottom: '1.25rem' }}>
                        <label style={{ display: 'block', fontSize: '0.85rem', fontWeight: '600', color: 'var(--text-secondary)', marginBottom: '0.5rem' }}>Qualification Name</label>
                        <input 
                            type="text" 
                            className="form-control"
                            placeholder="e.g. Graduate Degree" 
                            value={formState.name}
                            onChange={(e) => setFormState({ ...formState, name: e.target.value })}
                            required
                        />
                    </div>
                    <button type="submit" className="btn-primary" style={{ width: '100%' }}>
                        {formState.id ? 'Update Qualification' : 'Save Qualification'}
                    </button>
                    {formState.id && (
                        <button 
                            type="button" 
                            onClick={() => setFormState({ id: null, name: '' })}
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
                    <div className="text-center" style={{ padding: '2rem', color: 'var(--text-secondary)' }}>Loading qualifications...</div>
                ) : (
                    <DataTable 
                        columns={columns} 
                        data={qualifications} 
                        onEdit={handleEdit} 
                        onDelete={handleDelete} 
                    />
                )}
            </div>
        </div>
    );
}
