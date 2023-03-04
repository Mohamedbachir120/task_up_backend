<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Rapport d'activité</title>

    <!-- Scripts -->
</head>
<body>
        <h5>Direction : {{ $user->structurable->direction->name }}</h5>
        <h5>Département : {{ $user->structurable->name }}</h5>
        <h5>Nom : {{ $user->name }}</h5>
        <h5 style="text-align: center;text-decoration:underline;"> Rapport d'activité du mois de {{ $monthString }} </h5>

        <main>
          
                <table>
                    <thead>
                        <tr style="background: rgba(173, 216, 230, 0.521)">
                         <td>
                          <strong>  Projets </strong>
                        </td>    
                         <td>

                        <strong>   Tâches </strong>
                       </td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                        @foreach ($tasks as $task)
                            @if($loop->index ==  0 ||  $tasks[$loop->index - 1]->project->name != $task->project->name)
                            @if($loop->index != 0)
                            </td>  
                             </tr>
                             @endif
                                <tr>
                                    <td> <strong>  {{ $task->project->name }} </strong> </td>
                                    <td>
                            @endif    
                                
                             {{ $loop->iteration }} -  {{ $task->title }}
                                    <ul>
                                    @foreach ($task->sub_tasks as $subtask)
                                         <li>* {{ $subtask->title }}</li>   
                                    @endforeach
                                    </ul>
                                
                
                            @endforeach
                        </tr>
                    </tbody>
                </table>
        </main>
        <style>
         table{
            min-width: 100%;
         }   
         table,td {
            border: 1px solid grey;
            border-collapse: collapse;
            padding: 10px;
            }
         td:first-of-type{
            border-bottom:none; 
         }   
         td{
            vertical-align: top;
         }
         li{
            list-style: none;
         }
        </style>
</body>
</html>