<div class="card shadow-sm">
    <div class="card-body">
        <div id="timetableContainer"></div>
    </div>
</div>

<script>

function renderGrid(periods, days, entries) {

    let html = `<table class="table table-bordered text-center align-middle">`;
    html += `<thead><tr><th width="140">Day / Time</th>`;

    periods.forEach(p => {
        html += `
            <th>
                ${p.name}<br>
                <small>${p.start_time} - ${p.end_time}</small>
            </th>
        `;
    });

    html += `</tr></thead><tbody>`;

    days.forEach(day => {

        html += `<tr><td><strong>${day}</strong></td>`;

        periods.forEach(period => {

            const entry = entries.find(e =>
                e.day_of_week === day &&
                e.period_id === period.id
            );

            // Break Period
            if (period.is_break) {
                html += `<td class="bg-warning text-dark fw-bold">
                            BREAK
                         </td>`;
            }
            // Existing Entry
            else if (entry) {
                html += `
                    <td class="bg-light"
                        style="cursor:pointer"
                        onclick="openAssignModal('${day}', ${period.id}, ${entry.id})">

                        <strong>${entry.subject?.name ?? ''}</strong><br>
                        <small>${entry.teacher?.name ?? ''}</small>
                    </td>
                `;
            }
            // Empty Cell
            else {
                html += `
                    <td class="text-primary fw-bold"
                        style="cursor:pointer; font-size:18px"
                        onclick="openAssignModal('${day}', ${period.id}, null)">
                        +
                    </td>
                `;
            }

        });

        html += `</tr>`;
    });

    html += `</tbody></table>`;

    document.getElementById("timetableContainer").innerHTML = html;
}

</script>