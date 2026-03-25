@extends('layouts.student')

@section('title', 'Previous Fee Details')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">
        Previous Fee Details
    </h2>
    <p class="text-muted mb-0">
        View installment breakdown and payment receipts
    </p>
</div>

{{-- ================= SUMMARY CARD ================= --}}
<div class="card mb-4">
    <div class="card-body" id="summaryCard">
        <div class="text-center text-muted">Loading...</div>
    </div>
</div>

{{-- ================= INSTALLMENT CARD ================= --}}
<div class="card mb-4">
    <div class="card-body">
        <h4 class="card-title mb-3">Installment Breakdown</h4>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Installment</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Paid Date</th>
                    </tr>
                </thead>
                <tbody id="installmentTableBody">
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ================= PAYMENT RECEIPTS CARD ================= --}}
<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-3">Payment Receipts</h4>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Installment</th>
                        <th>Amount</th>
                        <th>Mode</th>
                        <th>Paid Date</th>
                        <th width="120">Receipt</th>
                    </tr>
                </thead>
                <tbody id="paymentTableBody">
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user") || "{}");

    if (!token || !user || user.role !== "student") {
        localStorage.clear();
        window.location.href = "/login";
        return;
    }

    // ================= HEADER LOAD =================
    apiRequest("GET", "/me")
    .done(function(res){
        document.getElementById("headerUserName").innerText =
            res.name || "-";
        document.getElementById("headerUserRole").innerText =
            "Student";
    });

    document.getElementById("logoutBtn")
    .addEventListener("click", function(){
        localStorage.clear();
        window.location.href = "/login";
    });

    // ================= GET ASSIGNMENT ID FROM URL =================
    const urlParts = window.location.pathname.split("/");
    const assignmentId = urlParts[urlParts.length - 1];

    loadDetails(assignmentId);

    function loadDetails(id) {

        apiRequest("GET", `/student/previous-fees/${id}`)
        .done(function(res){

            renderSummary(res.assignment);
            renderInstallments(res.installments);
            renderPayments(res.payments);

        })
        .fail(function(){
            alert("Failed to load fee details");
        });
    }

    // ================= SUMMARY =================
    function renderSummary(data) {

        const statusBadge =
            data.status === "PAID"
                ? `<span class="badge bg-success">${data.status}</span>`
                : `<span class="badge bg-warning text-dark">${data.status}</span>`;

        document.getElementById("summaryCard").innerHTML = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Academic Year:</strong> ${data.academic_year}<br>
                    <strong>Class:</strong> ${data.class_name}<br>
                    <strong>Fee Name:</strong> ${data.fee_name}
                </div>
                <div class="col-md-6 text-md-end">
                    <strong>Total:</strong> ₹ ${data.total_amount}<br>
                    <strong>Paid:</strong> ₹ ${data.paid_amount}<br>
                    <strong>Due:</strong> ₹ ${data.due_amount}<br>
                    ${statusBadge}
                </div>
            </div>
        `;
    }

    // ================= INSTALLMENTS =================
    function renderInstallments(installments) {

        const tbody = document.getElementById("installmentTableBody");
        tbody.innerHTML = "";

        if (!installments || installments.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        No installment structure available
                    </td>
                </tr>
            `;
            return;
        }

        installments.forEach(inst => {

            const badge =
                inst.status === "PAID"
                    ? `<span class="badge bg-success">PAID</span>`
                    : `<span class="badge bg-warning text-dark">PENDING</span>`;

            tbody.innerHTML += `
                <tr>
                    <td>Installment ${inst.installment_no}</td>
                    <td>₹ ${inst.amount}</td>
                    <td>${badge}</td>
                    <td>${inst.paid_at ?? '-'}</td>
                </tr>
            `;
        });
    }

    // ================= PAYMENTS =================
    function renderPayments(payments) {

        const tbody = document.getElementById("paymentTableBody");
        tbody.innerHTML = "";

        const successfulPayments =
            payments.filter(p => p.status === "SUCCESS");

        if (successfulPayments.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        No successful payments found
                    </td>
                </tr>
            `;
            return;
        }

        successfulPayments.forEach(p => {

            tbody.innerHTML += `
                <tr>
                    <td>
                        ${p.installment_no
                            ? "Installment " + p.installment_no
                            : "Full Payment"}
                    </td>
                    <td>₹ ${p.amount}</td>
                    <td>${p.payment_mode ?? '-'}</td>
                    <td>${p.paid_on ?? '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-primary"
                            onclick="downloadReceipt(${p.id})">
                            Download
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    // ================= DOWNLOAD RECEIPT =================
    window.downloadReceipt = function(paymentId) {

        fetch(`${API_BASE_URL}/payments/${paymentId}/receipt`, {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: "application/pdf"
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error("Receipt not available");
            }
            return response.blob();
        })
        .then(blob => {

            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");

            a.href = url;
            a.download = `Receipt_${paymentId}.pdf`;

            document.body.appendChild(a);
            a.click();
            a.remove();

            window.URL.revokeObjectURL(url);
        })
        .catch(err => {
            alert(err.message);
        });
    };

});
</script>
@endpush