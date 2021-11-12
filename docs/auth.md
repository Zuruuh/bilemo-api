# Authentication

## Routes

![](https://img.shields.io/badge/-POST-orange)  
Route: **/api/login**   
<table>
  <thead>
    <tr>
      <td>Key</td>
      <td>Value</td>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Description</td>
      <td>Login using your credentials</td>
    </tr>
    <tr>
      <td>Method(s)</td>
      <td>POST</td>
    </tr>
    <tr>
      <td>Security</td>
      <td>None</td>
    </tr>
    <tr>
      <td>Body</td>
      <td>
      <pre>
{
  "username": string,
  "password": string
}</pre>
      </td>
    </tr>
    <tr>
      <td>Output</td>
      <td>
      <pre>
{
  "token"?: string
}</pre>
      </td>
    </tr>
  </tbody>
</table>

## Usage 

While running the project locally, you can login using the test admin account.   
You can get your JsonWebToken by running the following request (example using cURL).   
<pre>
curl --location --insecure --request POST 'https://app.bilemo/api/login' \
--header 'Content-Type: application/json' \
--data-raw '{
    "username": "admin",
    "password": "password"
}'
</pre>

The token will always be in the "token" property of the response's json object.   

To use the token, add an "Authorization" header to your request, and set it's value to be "Bearer \<your-token\>".   
Here is another example request using cURL showing how to use the authorization header.
<pre>
curl --location --insecure --request GET 'https://app.bilemo/api/client' \
--header 'Content-Type: application/json' \
# replace here the {JsonWebToken} with your actual token
--header 'Authorization: Bearer {JsonWebToken}'
</pre>

When making an api call with a valid jwt, the server will always return a new token so you don't have to create a new one when yours expire. This can be used for api automation.   
Be careful, since the server will not return a new token if:   
- An uncatched error is thrown (Server will return a 500 http code).   
- The token in your request was invalid.   
