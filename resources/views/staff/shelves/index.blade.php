                    <tr>
                        <th>ID</th>
                        <th>Raf Adı</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shelves as $shelf)
                    <tr>
                        <td>{{ $shelf->id }}</td>
                        <td>{{ $shelf->name }}</td>
                        <td>
                            <a href="{{ route('staff.shelves.edit', $shelf->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('staff.shelves.destroy', $shelf->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu rafı silmek istediğinize emin misiniz?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody> 