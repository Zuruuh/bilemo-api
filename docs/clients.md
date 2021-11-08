# Clients

## Entity

Client entity schema:
```ts
{
  "id": int,
  "username": string,
  "roles": string[],
  "email": string
}
```

## Routes

![](https://img.shields.io/badge/-GET-brightgreen)  
Route: **/api/client**   
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
      {
       client: Client
      }
      </td>
    </tr>
  </tbody>
</table>
