const API_BASE_URL = "http://127.0.0.1:8000/api";

function apiRequest(method, url, data = {})
{
    const token =
        localStorage.getItem("auth_token");

    const headers =
    {
        "Accept": "application/json"
    };

    if (token && url !== "/login")
    {
        headers["Authorization"] =
            "Bearer " + token;
    }

    let fullUrl =
        API_BASE_URL + url;

    // FIX: properly append query params for GET
    if (method === "GET" && data && Object.keys(data).length > 0)
    {
        const params =
            new URLSearchParams(data);

        fullUrl += "?" + params.toString();
    }

    return $.ajax(
    {
        url: fullUrl,

        type: method,

        headers: headers,

        // Only send JSON body for non-GET
        data:
            method === "GET"
                ? null
                : JSON.stringify(data),

        contentType:
            method === "GET"
                ? undefined
                : "application/json",

        processData:
            method === "GET"
                ? true
                : false
    });
}