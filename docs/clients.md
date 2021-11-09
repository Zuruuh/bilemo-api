# Clients

## Entity

Client entity schema:
<pre>
{
  "id": int,
  "username": string,
  "roles": string[],
  "email": string
}
</pre>

## Routes

![](https://img.shields.io/badge/-GET-brightgreen)  
Route: **/api/clients/me**   
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
      <td>Returns information about your account</td>
    </tr>
    <tr>
      <td>Method(s)</td>
      <td>GET</td>
    </tr>
    <tr>
      <td>Security</td>
      <td>On</td>
    </tr>
    <tr>
      <td>Output</td>
      <td>
      <pre>
{
  client: Client
}</pre>
      </td>
    </tr>
  </tbody>
</table>
