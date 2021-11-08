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
      {
        "username": string,
        "password": string
      }
      </td>
    </tr>
    <tr>
      <td>Output</td>
      <td>
      {
        ?"token": string
      }
      </td>
    </tr>
  </tbody>
</table>