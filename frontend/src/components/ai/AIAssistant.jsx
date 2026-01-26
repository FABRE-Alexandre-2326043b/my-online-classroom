import React, { useState } from 'react';
import { aiService } from '../../services/api';

export default function AIAssistant({ courseId }) {
    const [question, setQuestion] = useState('');
    const [answer, setAnswer] = useState('');
    const [loading, setLoading] = useState(false);
    const [history, setHistory] = useState([]);

    const handleAsk = async (e) => {
        e.preventDefault();
        if (!question.trim()) return;

        setLoading(true);
        
        try {
            const response = await aiService.askQuestion(courseId, question);
            
            const newEntry = {
                question,
                answer: response.data.answer,
                timestamp: new Date()
            };

            setHistory(prev => [newEntry, ...prev]);
            setAnswer(response.data.answer);
            setQuestion('');
        } catch (error) {
            console.error('Erreur IA:', error);
            setAnswer('Désolé, une erreur est survenue.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="bg-gradient-to-br from-purple-50 to-blue-50 rounded-lg p-6 shadow-lg">
            <div className="flex items-center mb-4">
                <svg className="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} 
                          d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <h3 className="text-2xl font-bold text-gray-800">Assistant IA</h3>
            </div>
            
            <form onSubmit={handleAsk} className="space-y-4">
                <textarea
                    value={question}
                    onChange={(e) => setQuestion(e.target.value)}
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                    rows="3"
                    placeholder="Posez votre question sur le cours..."
                    disabled={loading}
                />
                
                <button
                    type="submit"
                    disabled={loading || !question.trim()}
                    className="w-full bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 disabled:bg-gray-400 font-medium transition"
                >
                    {loading ? 'Réflexion en cours...' : 'Demander à l\'IA'}
                </button>
            </form>
            
            {answer && (
                <div className="mt-6 p-4 bg-white rounded-lg border border-purple-200">
                    <h4 className="font-semibold text-purple-900 mb-2">Réponse :</h4>
                    <p className="text-gray-700 whitespace-pre-wrap">{answer}</p>
                </div>
            )}

            {history.length > 0 && (
                <div className="mt-6">
                    <h4 className="font-semibold text-gray-800 mb-3">Historique</h4>
                    <div className="space-y-3 max-h-64 overflow-y-auto">
                        {history.map((entry, idx) => (
                            <div key={idx} className="bg-white p-3 rounded-lg border border-gray-200">
                                <p className="text-sm font-medium text-gray-700">Q: {entry.question}</p>
                                <p className="text-xs text-gray-600 mt-1">R: {entry.answer.substring(0, 100)}...</p>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}