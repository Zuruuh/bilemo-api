# Users

## Entity

User entity schema:
```ts
{
    "id": int,
    "client": Client,
    "name": string,
    "balance": int,
}
```

The client property represents the client who owns the account (OneToMany), which means only the owner of an user should be able to manage it.

## Routes

![](https://img.shields.io/badge/-GET-brightgreen)  
Route: **/api/users**   
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
            <td>Returns all your users in a cursor paginated list</td>
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
            <td>Query</td>
            <td>
            Cursor?: int | The current pagination cursor (default: 0)
            </td>
        </tr>
        <tr>
            <td>Output</td>
            <td>
            {
             users: [
                 User, 
                 "cursor": int
                ]
            }
            </td>
        </tr>
    </tbody>
</table>

![](https://img.shields.io/badge/-GET-brightgreen)  
Route: **/api/users/{id}**   
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
            <td>Returns a specific user</td>
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
            <td>Params</td>
            <td>
            id: int | The searched user's id
            </td>
        </tr>
        <tr>
            <td>Output</td>
            <td>
            {
             user: User
            }
            </td>
        </tr>
    </tbody>
</table>

![](https://img.shields.io/badge/-POST-orange)  
Route: **/api/users/create**   
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
            <td>Creates a user</td>
        </tr>
        <tr>
            <td>Method(s)</td>
            <td>POST</td>
        </tr>
        <tr>
            <td>Security</td>
            <td>On</td>
        </tr>
        <tr>
            <td>Body</td>
            <td>
            {
                "name": string,
                "balance"?: int
            }
            </td>
        </tr>
    </tbody>
</table>

![](https://img.shields.io/badge/-DELETE-f22)  
Route: **/api/users/delete/{id}**   
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
            <td>Deletes a user</td>
        </tr>
        <tr>
            <td>Method(s)</td>
            <td>DELETE</td>
        </tr>
        <tr>
            <td>Security</td>
            <td>On</td>
        </tr>
        <tr>
            <td>Params</td>
            <td>
            id: int | The id of the user to delete
            </td>
        </tr>
    </tbody>
</table>

![](https://img.shields.io/badge/-PUT-blue) ![](https://img.shields.io/badge/-PATCH-fff)  
Route: **/api/users/edit/{id}**   
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
            <td>Deletes a user</td>
        </tr>
        <tr>
            <td>Method(s)</td>
            <td>PUT, PATCH</td>
        </tr>
        <tr>
            <td>Security</td>
            <td>On</td>
        </tr>
        <tr>
            <td>Params</td>
            <td>
            id: int | The id of the user to update
            </td>
        </tr>
        <tr>
            <td>Body</td>
            <td>
            {
                "name"?: string,
                "balance"?: int
            }
            </td>
        </tr>
    </tbody>
</table>