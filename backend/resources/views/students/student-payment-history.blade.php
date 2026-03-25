@extends('layouts.student')

@section('title', 'Payment History')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">
        My Payment History
    </h2>
    <p class="text-muted mb-0">
        View all successful fee payments and download receipts
    </p>
</div>

<div class="card">
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered" id="paymentHistoryTable">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Assignment</th>
                        <th>Installment</th>
                        <th>Amount</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th width="120">Receipt</th>
                    </tr>
                </thead>
                <tbody id="paymentHistoryTableBody">
                    <tr>
                        <td colspan="7" class="text-center text-muted">
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
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user || user.role !== "student") {
        localStorage.clear();
        window.location.href = "{{ url('/login') }}";
        return;
    }

    // ===============================
    // LOAD HEADER PROFILE
    // ===============================
    apiRequest("GET", "/me")
        .done(function (res) {

            document.getElementById("headerUserName").innerText =
                res.name ?? "Student";

            document.getElementById("headerUserRole").innerText =
                "Student";
        });

    // ===============================
    // LOGOUT
    // ===============================
    document.getElementById("logoutBtn")
        .addEventListener("click", function () {
            localStorage.clear();
            window.location.href = "{{ url('/login') }}";
        });

    // ===============================
    // LOAD PAYMENT HISTORY
    // ===============================
    loadPaymentHistory();

    function loadPaymentHistory() {

        const tbody = document.getElementById("paymentHistoryTableBody");

        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted">
                    Loading...
                </td>
            </tr>
        `;

        apiRequest("GET", "/student/payment-history")
            .done(res => {

                const payments = res.payments || [];
                tbody.innerHTML = "";

                if (payments.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                No payment records found
                            </td>
                        </tr>
                    `;
                    return;
                }

                payments.forEach(payment => {

                    const installmentText =
                        payment.installment_no
                            ? "Installment " + payment.installment_no
                            : "Full Payment";

                    const receiptBtn = payment.receipt_path
                        ? `
                            <button class="btn btn-sm btn-primary"
                                onclick="downloadReceipt(${payment.id})">
                                Download
                            </button>
                        `
                        : `
                            <button class="btn btn-sm btn-secondary" disabled>
                                N/A
                            </button>
                        `;

                    tbody.innerHTML += `
                        <tr>
                            <td>${payment.paid_on ?? "-"}</td>
                            <td>
                                <strong>#${payment.student_fee_assignment_id}</strong>
                            </td>
                            <td>${installmentText}</td>
                            <td>₹ ${payment.amount}</td>
                            <td>${payment.payment_mode}</td>
                            <td>
                                <span class="badge bg-success">
                                    ${payment.status}
                                </span>
                            </td>
                            <td>${receiptBtn}</td>
                        </tr>
                    `;
                });
            })
            .fail(() => {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7"
                            class="text-danger text-center">
                            Failed to load payment history
                        </td>
                    </tr>
                `;
            });
    }

    // ===============================
    // DOWNLOAD RECEIPT
    // ===============================
    window.downloadReceipt = function (paymentId) {

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