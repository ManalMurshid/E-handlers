document.addEventListener('DOMContentLoaded', () => {
    fetchPapers();

    function fetchPapers() {
        fetch('examiner_dashboard.php')
            .then(response => response.json())
            .then(data => {
                const papersGrid = document.getElementById('papers-grid');
                papersGrid.innerHTML = '';
                data.forEach(paper => {
                    const paperCard = document.createElement('div');
                    paperCard.classList.add('paper-card');
                    paperCard.innerHTML = `
                        <h2>${paper.course_name}</h2>
                        <p>Duration: ${paper.duration}</p>
                        <p>Batch: ${paper.batch}</p>
                        <div class="buttons">
                            <button onclick="editExam(${paper.id})">Edit</button>
                            <button onclick="viewReport(${paper.id})">View Reports</button>
                            <button onclick="deleteExam(${paper.id})">Delete</button>
                        </div>
                    `;
                    papersGrid.appendChild(paperCard);
                });
            });
    }

    window.editExam = function (examId) {
        window.location.href = `create_paper/edit_exam.php?id=${examId}`;
    }

    window.viewReport = function (examId) {
        const iframe = document.createElement('iframe');
        iframe.src = `view_reports/view_report.php?id=${examId}`;
        iframe.style.width = '100%';
        iframe.style.height = '600px';
        document.body.appendChild(iframe);
    }

    window.deleteExam = function (examId) {
        if (confirm('Are you sure you want to delete this exam paper?')) {
            fetch(`delete_exam.php?id=${examId}`, { method: 'DELETE' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Exam paper deleted successfully.');
                        fetchPapers();
                    } else {
                        alert('Failed to delete exam paper.');
                    }
                });
        }
    }
});
