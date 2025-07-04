<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $asunto }}</title>
</head>
<body style="background-color: #f4f4f4; margin: 0; padding: 20px; font-family: Arial, sans-serif;">

    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">

        <h2 style="text-align: center; color: #6B46C1; margin-bottom: 20px;">{{ $asunto }}</h2>

        @foreach ($bloques as $bloque)
            @if ($bloque['type'] === 'header')
                <h3 style="
                    text-align: center;
                    color: {{ $bloque['styles']['color'] ?? '#333' }};
                    font-weight: {{ $bloque['styles']['bold'] ? 'bold' : 'normal' }};
                    font-style: {{ $bloque['styles']['italic'] ? 'italic' : 'normal' }};
                    margin-top: 20px;
                    margin-bottom: 10px;
                ">
                    {{ $bloque['content'] }}
                </h3>
            @elseif ($bloque['type'] === 'text')
                <p style="
                    text-align: center;
                    color: {{ $bloque['styles']['color'] ?? '#555' }};
                    font-weight: {{ $bloque['styles']['bold'] ? 'bold' : 'normal' }};
                    font-style: {{ $bloque['styles']['italic'] ? 'italic' : 'normal' }};
                    line-height: 1.6;
                    margin: 10px 0;
                ">
                    {{ $bloque['content'] }}
                </p>
            @elseif ($bloque['type'] === 'divider')
                <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
            @elseif ($bloque['type'] === 'image' && !empty($bloque['imageUrl']))
                <div style="text-align: center; margin: 20px 0;">
                    <img src="{{ $bloque['imageUrl'] }}" alt="Imagen" style="max-width: 100%; height: auto; border-radius: 4px;">
                </div>
            @elseif ($bloque['type'] === 'columns' && !empty($bloque['imageUrls']))
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 20px;">
                    <tr>
                        @foreach ($bloque['imageUrls'] as $image)
                            <td style="width: 50%; text-align: center; padding: 5px;">
                                <img src="{{ $image }}" alt="Imagen columna" style="max-width: 100%; height: auto; border-radius: 4px;">
                            </td>
                        @endforeach
                    </tr>
                </table>
            @endif
        @endforeach

    </div>

</body>
</html>
